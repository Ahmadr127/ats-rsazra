<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterviewResultRequest;
use App\Logging\LogContext;
use App\Models\Application;
use App\Models\InterviewResult;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use App\Support\InterviewStageMap;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InterviewController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function decide(StoreInterviewResultRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        $user = $request->user();

        $stageKey = InterviewStageMap::stageKeyForDecider($user);
        abort_if(! $stageKey, 403);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load('stages');
        $interviewStage = $application->stages->firstWhere('key', $stageKey);

        abort_if(! $interviewStage, 404);

        if (! $interviewStage->status->isAdvanceable()) {
            return back()->withErrors(['interview' => 'Keputusan tidak dapat diberikan untuk tahap ini.']);
        }

        if ($interviewStage->interviewResult()->exists()) {
            return back()->withErrors(['interview' => 'Hasil wawancara sudah direkam sebelumnya.']);
        }

        if ($interviewStage->key === 'wawancara_user' && $interviewStage->interviewer_id !== $user->id) {
            abort(403);
        }

        abort_unless(
            $interviewStage->key !== InterviewStageMap::USER
                || $user->isInUnit($lowongan->unit_id)
                || $user->hasPermission(Permissions::VACANCY_VIEW_ORG),
            403
        );

        $keputusan = $request->input('keputusan');
        $catatan = $request->input('catatan');
        $ratings = $request->input('ratings', []);
        $readinessAnswers = $request->input('readiness_answers', []);

        try {
            DB::transaction(function () use ($interviewStage, $application, $user, $keputusan, $catatan, $ratings, $readinessAnswers): void {
                $result = InterviewResult::create([
                    'application_id' => $application->id,
                    'application_stage_id' => $interviewStage->id,
                    'interviewer_id' => $user->id,
                    'keputusan' => $keputusan,
                    'catatan' => $catatan,
                    'submitted_at' => now(),
                ]);

                foreach ($ratings as $rating) {
                    $result->ratings()->create([
                        'interview_template_id' => $rating['interview_template_id'],
                        'nama_kriteria' => $rating['nama_kriteria'],
                        'nilai' => $rating['nilai'],
                    ]);
                }

                foreach ($readinessAnswers as $answer) {
                    $result->readinessAnswers()->create([
                        'interview_template_id' => $answer['interview_template_id'],
                        'pertanyaan' => $answer['pertanyaan'],
                        'jawaban' => $answer['jawaban'],
                    ]);
                }

                match ($keputusan) {
                    'lulus' => $this->pipelineService->advance($application),
                    'gagal' => $this->pipelineService->fail($application),
                    'reserved' => $this->pipelineService->reserve($application),
                };
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['interview' => $e->getMessage()]);
        }

        Log::notice('Interview decision recorded', array_merge(LogContext::make(), [
            'application_id' => $application->id,
            'stage_key' => $stageKey,
            'keputusan' => $keputusan,
        ]));

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
