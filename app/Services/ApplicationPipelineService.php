<?php

namespace App\Services;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\DiscSubmission;
use App\Models\MbtiSubmission;
use App\Models\TestSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationPipelineService
{
    public function __construct(private readonly EmailNotificationService $emailNotificationService) {}

    /**
     * Advance the application to the next pipeline stage.
     *
     * @throws \RuntimeException when no active stage exists or already at last stage
     */
    public function advance(Application $application): void
    {
        $application->load(['candidate', 'vacancy']);

        DB::transaction(function () use ($application): void {
            $stages = $application->stages()->lockForUpdate()->orderBy('position')->get();

            $currentStage = $stages->first(
                fn ($s) => $s->status->isAdvanceable()
            );

            if (! $currentStage) {
                throw new \RuntimeException('Tidak ada tahap aktif yang dapat dilanjutkan.');
            }

            $this->guardExternalTestProof($application, $currentStage);

            $nextStage = $stages->first(
                fn ($s) => $s->position > $currentStage->position && $s->status === ApplicationStageStatus::Pending
            );

            $currentStage->update(['status' => ApplicationStageStatus::Selesai]);

            if ($nextStage) {
                $nextStage->update(['status' => ApplicationStageStatus::Aktif]);
            }
        });

        $application->load(['stages', 'candidate', 'vacancy.vacancyTest']);

        $nextStage = $application->stages
            ->where('status', ApplicationStageStatus::Aktif)
            ->first();

        if ($nextStage?->key === 'tes_kompetensi') {
            $vacancyTest = $application->vacancy->vacancyTest;

            if (! $vacancyTest) {
                throw new \RuntimeException('Konfigurasi tes kompetensi harus dibuat terlebih dahulu.');
            }

            $snapshot = $vacancyTest->latestSnapshot;

            if (! $snapshot) {
                throw new \RuntimeException('Konfigurasi tes kompetensi harus dibuat terlebih dahulu.');
            }

            $token = Str::uuid()->toString();
            TestSubmission::create([
                'application_id' => $application->id,
                'vacancy_test_snapshot_id' => $snapshot->id,
                'token' => $token,
            ]);

            try {
                $this->emailNotificationService->dispatch('tes_tersedia', $application->candidate->email, [
                    'nama_kandidat' => $application->candidate->nama_lengkap,
                    'judul_lowongan' => $application->vacancy->judul_posisi,
                    'link_tes' => route('tes.show', $token),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }

            return;
        }

        if ($nextStage?->key === 'tes_disc') {
            DiscSubmission::create([
                'application_id' => $application->id,
                'token' => Str::uuid()->toString(),
            ]);

            // Admin HR sends the external test link manually via the pipeline panel.
            return;
        }

        if ($nextStage?->key === 'tes_mbti') {
            MbtiSubmission::create([
                'application_id' => $application->id,
                'token' => Str::uuid()->toString(),
            ]);

            // Admin HR sends the external test link manually via the pipeline panel.
            return;
        }

        $wawancaraStages = ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur'];
        $silentStages = ['mcu', 'onboarding', ...$wawancaraStages];

        if ($nextStage === null || in_array($nextStage->key, $silentStages, true)) {
            return;
        }

        $currentStage = $application->stages
            ->where('status', ApplicationStageStatus::Selesai)
            ->sortByDesc('position')
            ->first();

        $skriningStages = ['skrining_cv_hr', 'skrining_cv_user'];
        $isFromSkrining = $currentStage && in_array($currentStage->key, $skriningStages, true);
        $nextIsNotSkrining = ! in_array($nextStage->key, $skriningStages, true);

        if ($isFromSkrining && $nextIsNotSkrining) {
            try {
                $this->emailNotificationService->dispatch('lolos_skrining_cv', $application->candidate->email, [
                    'nama_kandidat' => $application->candidate->nama_lengkap,
                    'judul_lowongan' => $application->vacancy->judul_posisi,
                    'link_status' => route('karier.lamaran.konfirmasi', $application->token),
                ]);
            } catch (\Throwable $e) {
                report($e);
            }

            return;
        }

        try {
            $this->emailNotificationService->dispatch('transisi_tahap', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'link_status' => route('karier.lamaran.konfirmasi', $application->token),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * External DISC/MBTI stages may only advance after the candidate uploads proof.
     */
    private function guardExternalTestProof(Application $application, ApplicationStage $currentStage): void
    {
        if (! in_array($currentStage->key, ['tes_disc', 'tes_mbti'], true)) {
            return;
        }

        $submission = $currentStage->key === 'tes_disc'
            ? $application->discSubmission()->first()
            : $application->mbtiSubmission()->first();

        if (! $submission?->proof_uploaded_at || ! $submission->hasProof()) {
            throw new \RuntimeException('Bukti tes wajib diunggah sebelum tahap ini dapat dilanjutkan.');
        }
    }

    /**
     * Park the application at the current stage (reserved) without advancing or failing.
     *
     * @throws \RuntimeException when no active stage exists
     */
    public function reserve(Application $application): void
    {
        $application->load(['candidate', 'vacancy']);

        DB::transaction(function () use ($application): void {
            $stages = $application->stages()->lockForUpdate()->orderBy('position')->get();

            $currentStage = $stages->first(
                fn ($s) => $s->status === ApplicationStageStatus::Aktif
            );

            if (! $currentStage) {
                throw new \RuntimeException('Tidak ada tahap aktif yang dapat ditangguhkan.');
            }

            $currentStage->update(['status' => ApplicationStageStatus::Reserved]);
        });
    }

    /**
     * Roll a recorded "lulus" decision back to "gagal" or "reserved" and
     * park the application at that stage again. Only safe while the next
     * stage has not been filled.
     */
    public function retract(Application $application, ApplicationStage $stage, string $keputusan): void
    {
        $application->load(['candidate', 'vacancy']);

        DB::transaction(function () use ($application, $stage, $keputusan): void {
            $stages = $application->stages()->lockForUpdate()->orderBy('position')->get();

            $currentStage = $stages->firstWhere('id', $stage->id);
            $nextStage = $stages->first(
                fn ($s) => $s->position > $currentStage->position
            );

            if ($nextStage && $nextStage->status !== ApplicationStageStatus::Pending) {
                $nextStage->update(['status' => ApplicationStageStatus::Pending]);
            }

            $currentStage->update([
                'status' => $keputusan === 'gagal'
                    ? ApplicationStageStatus::Gagal
                    : ApplicationStageStatus::Reserved,
            ]);
        });

        $emailKey = match ($keputusan) {
            'gagal' => 'kandidat_ditolak',
            'reserved' => 'transisi_tahap',
            default => null,
        };

        if (! $emailKey) {
            return;
        }

        try {
            $this->emailNotificationService->dispatch($emailKey, $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
                'link_status' => route('karier.lamaran.konfirmasi', $application->token),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Fail/reject the application at the current stage.
     *
     * @throws \RuntimeException when no active stage exists
     */
    public function fail(Application $application): void
    {
        $application->load(['candidate', 'vacancy']);

        DB::transaction(function () use ($application): void {
            $stages = $application->stages()->lockForUpdate()->orderBy('position')->get();

            $currentStage = $stages->first(
                fn ($s) => $s->status->isAdvanceable()
            );

            if (! $currentStage) {
                throw new \RuntimeException('Tidak ada tahap aktif yang dapat digagalkan.');
            }

            $currentStage->update(['status' => ApplicationStageStatus::Gagal]);
        });

        try {
            $this->emailNotificationService->dispatch('kandidat_ditolak', $application->candidate->email, [
                'nama_kandidat' => $application->candidate->nama_lengkap,
                'judul_lowongan' => $application->vacancy->judul_posisi,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
