<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Http\Requests\UploadTestProofRequest;
use App\Logging\LogContext;
use App\Models\MbtiSubmission;
use App\Services\ApplicationPipelineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MbtiTestController extends Controller
{
    public function __construct(private readonly ApplicationPipelineService $pipelineService) {}

    public function show(string $token): View
    {
        $submission = MbtiSubmission::with([
            'application.stages',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        $stage = $submission->application->stages->firstWhere('key', 'tes_mbti');
        abort_if(! $stage, 404);

        return view('external-tests.show', [
            'submission' => $submission,
            'stage' => $stage,
            'testLabel' => 'MBTI',
            'submitRoute' => 'tes-mbti.submit',
        ]);
    }

    public function submit(UploadTestProofRequest $request, string $token): RedirectResponse
    {
        $submission = MbtiSubmission::with([
            'application.stages',
            'application.candidate',
            'application.vacancy',
        ])->where('token', $token)->firstOrFail();

        $stage = $submission->application->stages->firstWhere('key', 'tes_mbti');
        abort_if(! $stage, 404);

        if ($stage->status === ApplicationStageStatus::Selesai) {
            return redirect()->route('tes-mbti.show', $token);
        }

        if ($stage->status === ApplicationStageStatus::Gagal) {
            return back()->withErrors(['proofs' => 'Tahap tes ini sudah ditolak dan tidak dapat dilanjutkan.']);
        }

        if (! $stage->status->isAdvanceable()) {
            return back()->withErrors(['proofs' => 'Tahap tes ini sedang tidak aktif.']);
        }

        if (! $submission->link_tes) {
            return back()->withErrors(['proofs' => 'Link tes belum tersedia. Hubungi tim HR.']);
        }

        $newPaths = [];
        foreach ($request->file('proofs', []) as $file) {
            $newPaths[] = $file->store("test-proofs/{$submission->application_id}", 'public');
        }

        $oldPaths = $submission->proof_paths ?? [];

        $submission->update([
            'proof_paths' => $newPaths,
            'proof_uploaded_at' => now(),
        ]);

        Storage::disk('public')->delete($oldPaths);

        try {
            $this->pipelineService->advance($submission->application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['proofs' => 'Bukti tes tersimpan, tetapi pipeline gagal dilanjutkan: '.$e->getMessage()]);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['proofs' => 'Bukti tes tersimpan, tetapi pipeline gagal dilanjutkan. Silakan unggah ulang bukti.']);
        }

        Log::info('MBTI test proof uploaded', array_merge(LogContext::make(), [
            'submission_id' => $submission->id,
            'application_id' => $submission->application_id,
            'proof_count' => count($newPaths),
        ]));

        return redirect()
            ->route('tes-mbti.show', $token)
            ->with('success', 'Bukti tes MBTI berhasil dikirim. Tahap pipeline otomatis dilanjutkan.');
    }
}
