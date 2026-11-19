<?php

namespace App\Actions;

use App\Enums\ApplicationStageStatus;
use App\Enums\VacancyStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Unit;
use App\Models\Vacancy;
use Illuminate\Support\Collection;

class GetRecruitmentMetrics
{
    /**
     * @param  array{date_from: ?string, date_to: ?string, unit_id: ?int, vacancy_id: ?int}  $filters
     * @param  bool  $includeUnitOptions  Whether to load the unit selector options. Unit-tier
     *                                    dashboards hide the selector, so the list is skipped to
     *                                    avoid both the query and leaking other units' names.
     * @return array<string, mixed>
     */
    public function execute(array $filters, bool $includeUnitOptions = true): array
    {
        $applicationQuery = Application::query()
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('vacancy_id', $id))
            ->when(
                $filters['unit_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vacancy', fn ($vq) => $vq->where('unit_id', $id))
            );

        $applicationIds = $applicationQuery->select('id');

        return [
            'totalApplications' => $applicationQuery->count(),
            'inProcess' => $this->inProcess($applicationIds),
            'accepted' => $this->accepted($applicationIds),
            'openVacancies' => $this->openVacancies($filters),
            'vacancySummary' => $this->vacancySummary($filters),
            'units' => $includeUnitOptions
                ? Unit::select('id', 'nama')->orderBy('nama')->get()
                : new Collection,
            'vacancies' => Vacancy::query()
                ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
                ->select('id', 'judul_posisi')
                ->orderBy('judul_posisi')
                ->get(),
        ];
    }

    private function inProcess($applicationIds): int
    {
        return ApplicationStage::whereIn('application_id', $applicationIds)
            ->whereIn('status', [ApplicationStageStatus::Aktif, ApplicationStageStatus::Reserved])
            ->distinct('application_id')
            ->count('application_id');
    }

    private function accepted($applicationIds): int
    {
        return ApplicationStage::whereIn('application_id', $applicationIds)
            ->where('key', 'onboarding')
            ->where('status', ApplicationStageStatus::Selesai)
            ->count();
    }

    private function openVacancies(array $filters): int
    {
        return Vacancy::query()
            ->where('status', VacancyStatus::Published)
            ->where('tenggat_lamaran', '>=', now()->toDateString())
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->count();
    }

    private function vacancySummary(array $filters)
    {
        return Vacancy::query()
            ->when($filters['unit_id'] ?? null, fn ($q, $id) => $q->where('unit_id', $id))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
            ->withCount([
                'applications as total_pelamar' => function ($q) use ($filters): void {
                    $q->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
                'applications as posisi_terisi' => function ($q) use ($filters): void {
                    $q->whereHas('stages', fn ($sq) => $sq->where('key', 'onboarding')->where('status', ApplicationStageStatus::Selesai))
                        ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));
                },
            ])
            ->with('unit')
            ->latest()
            ->get();
    }
}
