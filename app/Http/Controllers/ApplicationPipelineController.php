<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendExternalTestRequest;
use App\Models\Application;
use App\Models\DiscSubmission;
use App\Models\MbtiSubmission;
use App\Models\Vacancy;
use App\Services\ApplicationPipelineService;
use App\Services\EmailNotificationService;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ApplicationPipelineController extends Controller
{
    public function __construct(
        private readonly ApplicationPipelineService $pipelineService,
        private readonly EmailNotificationService $emailNotificationService,
    ) {}

    public function advance(Vacancy $lowongan, Application $application): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::APPLICATION_ADVANCE);

        try {
            $this->pipelineService->advance($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pipeline' => $e->getMessage()]);
        }

        return redirect()->route('lowongan.pipeline', $lowongan)->with('success', 'Kandidat berhasil dilanjutkan ke tahap berikutnya.');
    }

    public function fail(Vacancy $lowongan, Application $application): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::APPLICATION_FAIL);

        try {
            $this->pipelineService->fail($application);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['pipeline' => $e->getMessage()]);
        }

        return redirect()->route('lowongan.pipeline', $lowongan)->with('success', 'Kandidat telah ditolak dari pipeline.');
    }

    public function sendDiscLink(SendExternalTestRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        return $this->sendExternalTestLink($request, $lowongan, $application, 'tes_disc');
    }

    public function sendMbtiLink(SendExternalTestRequest $request, Vacancy $lowongan, Application $application): RedirectResponse
    {
        return $this->sendExternalTestLink($request, $lowongan, $application, 'tes_mbti');
    }

    private function sendExternalTestLink(
        SendExternalTestRequest $request,
        Vacancy $lowongan,
        Application $application,
        string $stageKey,
    ): RedirectResponse {
        $request->user()->requirePermission(Permissions::TEST_SEND_LINK);

        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $application->load(['candidate', 'vacancy', 'stages']);

        $stage = $application->stages->firstWhere('key', $stageKey);
        abort_if(! $stage, 404);

        if (! $stage->status->isAdvanceable()) {
            return back()->withErrors(['link_tes' => 'Tahap tes sudah selesai dan tidak dapat diubah.']);
        }

        $isDisc = $stageKey === 'tes_disc';
        $submission = $isDisc
            ? DiscSubmission::firstOrCreate(
                ['application_id' => $application->id],
                ['token' => (string) Str::uuid()],
            )
            : MbtiSubmission::firstOrCreate(
                ['application_id' => $application->id],
                ['token' => (string) Str::uuid()],
            );

        if ($submission->proof_uploaded_at) {
            return back()->withErrors(['link_tes' => 'Kandidat sudah mengunggah bukti tes. Link tidak dapat diubah lagi.']);
        }

        $submission->update([
            'link_tes' => $request->validated('link_tes'),
            'catatan' => $request->validated('catatan'),
            'sent_at' => now(),
        ]);

        try {
            $this->emailNotificationService->dispatch(
                $isDisc ? 'tes_disc_tersedia' : 'tes_mbti_tersedia',
                $application->candidate->email,
                [
                    'nama_kandidat' => $application->candidate->nama_lengkap,
                    'judul_lowongan' => $application->vacancy->judul_posisi,
                    'link_tes' => $submission->link_tes,
                    'link_upload' => route($isDisc ? 'tes-disc.show' : 'tes-mbti.show', $submission->token),
                    'catatan' => $submission->catatan ?? '',
                ],
            );
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['email' => 'Link tersimpan, tetapi email gagal dikirim. Silakan coba kirim ulang.']);
        }

        return back()->with('success', 'Link tes '.($isDisc ? 'DISC' : 'MBTI').' berhasil disimpan dan dikirim ke kandidat.');
    }
}
