<?php

namespace App\Services;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Vacancy;
use Illuminate\Support\Collection;

class CallbackCandidateFinder
{
    /**
     * Stage keys treated as "CV screening". Failures at these stages are hidden
     * under the default (past-screening) filter.
     *
     * @var list<string>
     */
    public const SCREENING_STAGE_KEYS = ['skrining_cv_hr', 'skrining_cv_user'];

    /**
     * Build the callback list for a target Vacancy: one row per prior Gagal or
     * Reserved application under the same JobTemplate, plus applications parked
     * in the target Vacancy itself, with per-candidate badges.
     *
     * Hard-excludes candidates already hired (completed onboarding) and
     * candidates who self-applied to the target Vacancy without an invite.
     *
     * @return Collection<int, array{application: Application, failed_stage_label: string, source_status: ApplicationStageStatus, is_reserved: bool, invited: bool, responded: bool, active_elsewhere: bool}>
     */
    public function forVacancy(Vacancy $vacancy, bool $includeScreening = false): Collection
    {
        $applications = $this->eligibleApplications($vacancy, $includeScreening);
        $candidateIds = $applications->pluck('candidate_id')->unique();

        $invitedIds = $this->invitedIds($vacancy, $candidateIds);
        $appliedToTargetIds = $this->appliedToTargetIds($vacancy, $candidateIds);
        $activeElsewhereIds = $this->activeElsewhereIds($vacancy, $candidateIds);

        return $applications->map(function (Application $application) use ($invitedIds, $appliedToTargetIds, $activeElsewhereIds) {
            $candidateId = $application->candidate_id;
            $sourceStage = $this->sourceStage($application);

            return [
                'application' => $application,
                'failed_stage_label' => $sourceStage?->nama ?? '—',
                'source_status' => $sourceStage?->status ?? ApplicationStageStatus::Gagal,
                'is_reserved' => $sourceStage?->status === ApplicationStageStatus::Reserved,
                'invited' => $invitedIds->has($candidateId),
                'responded' => $appliedToTargetIds->has($candidateId),
                'active_elsewhere' => $activeElsewhereIds->has($candidateId),
            ];
        })->values();
    }

    /**
     * Eligible candidate ids for the write path. Uses the widest set
     * (includeScreening): the screening filter is a view toggle, not an
     * eligibility rule, so screening-stage failures stay invitable.
     *
     * @return list<int>
     */
    public function eligibleCandidateIds(Vacancy $vacancy): array
    {
        return $this->eligibleApplications($vacancy, includeScreening: true)
            ->pluck('candidate_id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Latest resumable (Reserved) application per candidate, keyed by candidate id.
     *
     * @return array<int, Application>
     */
    public function resumableApplications(Vacancy $vacancy): array
    {
        return $this->eligibleApplications($vacancy, includeScreening: true)
            ->filter(fn (Application $application) => $application->stages
                ->contains(fn ($stage) => $stage->status === ApplicationStageStatus::Reserved))
            ->sortByDesc('created_at')
            ->keyBy('candidate_id')
            ->all();
    }

    /**
     * Callback applications: prior Gagal/Reserved from other vacancies under the
     * same JobTemplate, plus applications parked in the target Vacancy itself
     * (Reserved to resume, or Gagal after a re-application) so their process can
     * simply be resumed (or re-invited) from this page.
     *
     * @return Collection<int, Application>
     */
    private function eligibleApplications(Vacancy $vacancy, bool $includeScreening): Collection
    {
        $stageFilter = function ($query, array $statuses) use ($includeScreening): void {
            $query->whereIn('status', $statuses);

            if (! $includeScreening) {
                $query->whereNotIn('key', self::SCREENING_STAGE_KEYS);
            }
        };

        $prior = Application::query()
            ->whereHas('vacancy', function ($query) use ($vacancy) {
                $query->where('job_template_id', $vacancy->job_template_id)
                    ->whereKeyNot($vacancy->id);
            })
            ->whereHas('stages', fn ($query) => $stageFilter($query, [
                ApplicationStageStatus::Gagal,
                ApplicationStageStatus::Reserved,
            ]))
            ->with(['candidate', 'stages', 'vacancy:id,judul_posisi'])
            ->get();

        // Failures parked in the target Vacancy itself (re-application after an
        // invite that failed again, or a decision recorded here) are callback
        // candidates as well — not only Reserved ones.
        $current = Application::query()
            ->where('vacancy_id', $vacancy->id)
            ->whereHas('stages', fn ($query) => $stageFilter($query, [
                ApplicationStageStatus::Gagal,
                ApplicationStageStatus::Reserved,
            ]))
            ->with(['candidate', 'stages', 'vacancy:id,judul_posisi'])
            ->get();

        $applications = $prior->concat($current)->unique('id')->values();
        $candidateIds = $applications->pluck('candidate_id')->unique();
        $invitedIds = $this->invitedIds($vacancy, $candidateIds);
        $appliedToTargetIds = $this->appliedToTargetIds($vacancy, $candidateIds);

        return $this->rejectIneligible(
            $applications,
            $candidateIds,
            $invitedIds,
            $appliedToTargetIds,
            $current->pluck('id'),
        );
    }

    /**
     * The stage that qualified this application for the callback list: Gagal is
     * preferred over Reserved so a rejected candidate can never "resume".
     */
    private function sourceStage(Application $application): ?ApplicationStage
    {
        return $application->stages->firstWhere('status', ApplicationStageStatus::Gagal)
            ?? $application->stages->firstWhere('status', ApplicationStageStatus::Reserved);
    }

    /**
     * Drop candidates who are already hired or who self-applied to the target
     * Vacancy without an invite.
     *
     * @param  Collection<int, Application>  $applications
     * @param  Collection<int, int>  $candidateIds
     * @param  Collection<int, int>  $invitedIds  candidate_id => index
     * @param  Collection<int, int>  $appliedToTargetIds  candidate_id => index
     * @param  Collection<int, int>  $targetApplicationIds  applications already in the target Vacancy
     * @return Collection<int, Application>
     */
    private function rejectIneligible(
        Collection $applications,
        Collection $candidateIds,
        Collection $invitedIds,
        Collection $appliedToTargetIds,
        Collection $targetApplicationIds,
    ): Collection {
        $hiredIds = $this->hiredIds($candidateIds);

        return $applications->reject(function (Application $application) use ($hiredIds, $invitedIds, $appliedToTargetIds, $targetApplicationIds) {
            $candidateId = $application->candidate_id;

            // Hard exclude: already hired (completed onboarding).
            if ($hiredIds->has($candidateId)) {
                return true;
            }

            // The application itself is the one parked in the target Vacancy.
            if ($targetApplicationIds->contains($application->id)) {
                return false;
            }

            // Hard exclude: self-applied to the target Vacancy without an invite.
            return $appliedToTargetIds->has($candidateId) && ! $invitedIds->has($candidateId);
        });
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function invitedIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return $vacancy->callbackInvites()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function appliedToTargetIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return $vacancy->applications()
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function hiredIds(Collection $candidateIds): Collection
    {
        return Application::query()
            ->whereIn('candidate_id', $candidateIds)
            ->whereHas('onboardingResult')
            ->pluck('candidate_id')
            ->flip();
    }

    /**
     * "Active elsewhere" = an in-progress application in a different Vacancy: it
     * has a live (Aktif/Reserved) stage and no Gagal stage.
     *
     * @param  Collection<int, int>  $candidateIds
     * @return Collection<int, int> candidate_id => index
     */
    private function activeElsewhereIds(Vacancy $vacancy, Collection $candidateIds): Collection
    {
        return Application::query()
            ->whereIn('candidate_id', $candidateIds)
            ->where('vacancy_id', '!=', $vacancy->id)
            ->whereHas('stages', function ($query) {
                $query->whereIn('status', [
                    ApplicationStageStatus::Aktif,
                    ApplicationStageStatus::Reserved,
                ]);
            })
            ->whereDoesntHave('stages', function ($query) {
                $query->where('status', ApplicationStageStatus::Gagal);
            })
            ->pluck('candidate_id')
            ->flip();
    }
}
