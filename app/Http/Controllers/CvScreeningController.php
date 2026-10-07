<?php

namespace App\Http\Controllers;

use App\Http\Requests\CvScreeningDecisionRequest;
use App\Models\Application;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CvScreeningController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function decide(CvScreeningDecisionRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        $user = $request->user();
        $user->requirePermission(Permissions::SCREENING_DECIDE);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load('stages');
        $screeningStage = $application->stages
            ->first(fn ($stage) => in_array($stage->key, ['skrining_cv_hr', 'skrining_cv_user'], true)
                && $stage->status->isAdvanceable());

        if (! $screeningStage) {
            return back()->withErrors(['screening' => 'Keputusan tidak dapat diberikan untuk tahap ini.']);
        }

        if ($screeningStage->key === 'skrining_cv_hr') {
            if (! $user->hasPermission(Permissions::VACANCY_VIEW_ORG)) {
                return back()->withErrors(['screening' => 'Keputusan tidak dapat diberikan untuk tahap ini.']);
            }
        } else {
            abort_unless($user->isInUnit($lowongan->unit_id) || $user->hasPermission(Permissions::VACANCY_VIEW_ORG), 403);
        }

        $catatan = $request->input('catatan');
        $keputusan = $request->input('keputusan');

        try {
            DB::transaction(function () use ($screeningStage, $catatan, $keputusan, $application, $user): void {
                $screeningStage->update(['catatan' => $catatan, 'reviewed_by' => $user->id]);

                match ($keputusan) {
                    'lulus' => $this->pipelineService->advance($application),
                    'gagal' => $this->pipelineService->fail($application),
                    'reserved' => $this->pipelineService->reserve($application),
                };
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['screening' => $e->getMessage()]);
        }

        $label = match ($keputusan) {
            'lulus' => 'diloloskan ke tahap berikutnya',
            'gagal' => 'ditolak dari pipeline',
            'reserved' => 'ditangguhkan',
        };

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', "Kandidat berhasil {$label}.");
    }
}
