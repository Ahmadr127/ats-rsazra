<?php

namespace App\Http\Controllers;

use App\Enums\InterviewTemplateType;
use App\Logging\LogContext;
use App\Models\Application;
use App\Models\Permission;
use App\Models\User;
use App\Models\Vacancy;
use App\Support\InterviewStageMap;
use App\Support\Permissions;
use Barryvdh\DomPDF\Facade\Pdf;
use iio\libmergepdf\Merger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class VacancyPipelineController extends Controller
{
    private static function authorizeCandidateDetail(User $user, Vacancy $lowongan): void
    {
        $user->requirePermission(Permissions::VACANCY_CANDIDATE_DETAIL);

        if (! $user->hasPermission(Permissions::VACANCY_VIEW_ORG)) {
            abort_unless($user->isInUnit($lowongan->unit_id), 403);
        }
    }

    public function index(Request $request, Vacancy $lowongan): View
    {
        self::authorizeCandidateDetail($request->user(), $lowongan);

        $lowongan->load(['unit', 'workflowTemplateSnapshot.stages']);

        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages->sortBy('position')->values();

        $query = Application::with(['candidate', 'stages'])
            ->where('vacancy_id', $lowongan->id);

        if ($search = $request->query('search')) {
            $query->whereHas('candidate', fn ($q) => $q->where('nama_lengkap', 'ilike', "%{$search}%"));
        }

        if ($stageFilter = $request->query('stage')) {
            $query->whereHas('stages', fn ($q) => $q
                ->where('key', $stageFilter)
                ->where('status', '!=', 'pending')
            );
        }

        $statusFilter = $request->query('status');
        if ($statusFilter === 'gagal') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'gagal'));
        } elseif ($statusFilter === 'aktif') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'aktif'));
        } elseif ($statusFilter === 'ditangguhkan') {
            $query->whereHas('stages', fn ($q) => $q->where('status', 'reserved'));
        } elseif ($statusFilter === 'selesai') {
            $query->whereDoesntHave('stages', fn ($q) => $q->whereIn('status', ['pending', 'aktif', 'reserved', 'gagal']));
        } elseif ($statusFilter === 'menunggu') {
            $query->whereDoesntHave('stages', fn ($q) => $q->whereIn('status', ['aktif', 'reserved', 'selesai', 'gagal']));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('vacancies.pipeline', compact('lowongan', 'applications', 'snapshotStages', 'stageFilter', 'statusFilter'));
    }

    public function showApplication(Request $request, Vacancy $lowongan, Application $application): View
    {
        self::authorizeCandidateDetail($request->user(), $lowongan);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $lowongan->load(['unit', 'workflowTemplateSnapshot.stages']);

        $application->load([
            'candidate.formalEducations',
            'candidate.informalEducations',
            'candidate.workExperiences',
            'candidate.organizationExperiences',
            'candidate.siblings',
            'candidate.spouses',
            'candidate.children',
            'candidate.languageSkills',
            'candidate.achievements',
            'candidate.applications.vacancy.unit',
            'stages.interviewResult.ratings.interviewTemplate',
            'stages.interviewResult.readinessAnswers.interviewTemplate',
            'stages.interviewer',
            'stages.mcuResult',
            'testSubmission.answers.question.options',
            'testSubmission.answers.selectedOption',
            'testSubmission.snapshot',
            'discSubmission.result',
            'mbtiSubmission.result',
            'offeringLetter',
            'mcuResult',
            'onboardingResult',
        ]);

        $snapshotStages = $lowongan->workflowTemplateSnapshot->stages->sortBy('position')->values();
        $currentStage = $application->currentStage();

        $assignedTemplates = collect();
        $assignedReadinessTemplates = collect();
        $interviewStageKeys = ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur'];

        if ($currentStage && in_array($currentStage->key, $interviewStageKeys, true)) {
            $assignedTemplates = $lowongan->interviewTemplates()
                ->wherePivot('stage_key', $currentStage->key)
                ->where('tipe', InterviewTemplateType::KriteriaPenilaian)
                ->with('items')
                ->get();

            $assignedReadinessTemplates = $lowongan->interviewTemplates()
                ->wherePivot('stage_key', $currentStage->key)
                ->where('tipe', InterviewTemplateType::Kesiapan)
                ->with('items')
                ->get();
        }

        $priorInterviewStageKeys = [];
        if ($currentStage) {
            $priorInterviewStageKeys = match ($currentStage->key) {
                'wawancara_manajer_hr' => ['wawancara_user'],
                'wawancara_direktur' => ['wawancara_user', 'wawancara_manajer_hr'],
                default => [],
            };
        }

        $priorInterviews = $application->stages
            ->whereIn('key', $priorInterviewStageKeys)
            ->filter(fn ($s) => $s->interviewResult !== null);

        $testAllReviewed = false;
        if ($application->testSubmission?->submitted_at) {
            $testAllReviewed = $application->testSubmission->answers->every(fn ($a) => $a->is_reviewed);
        }

        $user = $request->user();
        $isUserPic = true;
        $picLabel = null;

        if ($currentStage) {
            [$isUserPic, $picLabel] = match (true) {
                $currentStage->key === 'skrining_cv_hr' => [$user->hasPermission(Permissions::SCREENING_DECIDE), 'Admin HR'],
                $currentStage->key === 'skrining_cv_user' => [
                    $user->hasPermission(Permissions::SCREENING_DECIDE) && ($user->isInUnit($lowongan->unit_id) || $user->hasPermission(Permissions::VACANCY_VIEW_ORG)),
                    'Tim Unit '.$lowongan->unit->nama,
                ],
                $currentStage->key === 'tes_kompetensi' => [$user->hasPermission(Permissions::TEST_DECIDE), 'Admin HR'],
                $currentStage->key === 'wawancara_user' => [
                    $user->hasPermission(Permissions::INTERVIEW_SCHEDULE) || ($currentStage->interviewer_id !== null && $currentStage->interviewer_id === $user->id),
                    $currentStage->interviewer_id ? ($currentStage->interviewer?->name ?? 'Pewawancara') : 'Admin HR',
                ],
                $currentStage->key === InterviewStageMap::MANAGER => [$user->hasPermission(Permissions::INTERVIEW_DECIDE_MANAGER), 'Manajer HR'],
                $currentStage->key === InterviewStageMap::DIRECTOR => [$user->hasPermission(Permissions::INTERVIEW_DECIDE_DIRECTOR), 'Direktur'],
                $currentStage->key === 'surat_penawaran' => [$user->hasPermission(Permissions::OFFERING_MANAGE), 'Admin HR'],
                $currentStage->key === 'mcu' => [$user->hasAnyPermission([Permissions::MCU_SCHEDULE, Permissions::MCU_DECIDE]), 'Admin HR'],
                $currentStage->key === 'onboarding' => [$user->hasAnyPermission([Permissions::ONBOARDING_INVITE, Permissions::ONBOARDING_COMPLETE]), 'Admin HR'],
                default => [true, null],
            };
        }

        $eligibleInterviewers = collect();
        if ($currentStage?->key === 'wawancara_user') {
            $deciderRoleIds = Permission::roleIdsWith(Permissions::INTERVIEW_DECIDE_USER);
            $eligibleInterviewers = User::where('is_active', true)
                ->whereIn('role_id', $deciderRoleIds === [] ? [-1] : $deciderRoleIds)
                ->whereHas('employee', fn ($q) => $q->where('unit_id', $lowongan->unit_id))
                ->get();
        }

        return view('vacancies.pipeline-show', compact(
            'lowongan',
            'application',
            'snapshotStages',
            'currentStage',
            'assignedTemplates',
            'assignedReadinessTemplates',
            'priorInterviews',
            'testAllReviewed',
            'isUserPic',
            'picLabel',
            'eligibleInterviewers',
        ));
    }

    public function exportPdf(Vacancy $lowongan, Application $application): Response
    {
        self::authorizeCandidateDetail(auth()->user(), $lowongan);
        abort_if($application->vacancy_id !== $lowongan->id, 404);

        Log::info('Candidate pipeline PDF exported', array_merge(LogContext::make(), [
            'vacancy_id' => $lowongan->id,
            'application_id' => $application->id,
        ]));

        $lowongan->load(['unit']);
        $application->load([
            'candidate.formalEducations',
            'candidate.informalEducations',
            'candidate.workExperiences',
            'candidate.organizationExperiences',
            'candidate.siblings',
            'candidate.spouses',
            'candidate.children',
            'candidate.languageSkills',
            'candidate.achievements',
            'discSubmission.result',
            'mbtiSubmission.result',
            'socialMediaAccounts',
            'references',
        ]);

        $dataPdf = Pdf::loadView('vacancies.pdf.kandidat', compact('application', 'lowongan'))
            ->setPaper('a4')
            ->output();

        $pdfsToMerge = [$dataPdf];

        foreach ([$application->cv_path, $application->str_sip_path] as $path) {
            if (! $path) {
                continue;
            }

            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                continue;
            }

            $contents = Storage::get($path);
            if ($contents) {
                $pdfsToMerge[] = $contents;
            }
        }

        if (count($pdfsToMerge) > 1) {
            try {
                $merger = new Merger;
                foreach ($pdfsToMerge as $pdf) {
                    $merger->addRaw($pdf);
                }
                $output = $merger->merge();
            } catch (\Exception) {
                $output = $dataPdf;
            }
        } else {
            $output = $dataPdf;
        }

        $candidate = $application->candidate;
        $filename = 'Kandidat_'.Str::slug($candidate->nama_lengkap).'_'.Str::slug($lowongan->judul_posisi).'_'.now()->format('Ymd').'.pdf';

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
