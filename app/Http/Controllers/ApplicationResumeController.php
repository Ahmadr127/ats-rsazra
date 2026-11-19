<?php

namespace App\Http\Controllers;

use App\Enums\ApplicationStageStatus;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\InterviewResult;
use App\Models\Vacancy;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ApplicationResumeController extends Controller
{
    public function resume(Vacancy $lowongan, Application $application): RedirectResponse
    {
        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $stage = $application->stages()
            ->whereIn('status', [ApplicationStageStatus::Reserved->value, ApplicationStageStatus::Gagal->value])
            ->orderBy('position')
            ->first();

        if (! $stage) {
            return back()->withErrors(['resume' => 'Hanya kandidat dengan status Tunda atau Gagal yang dapat dilanjutkan.']);
        }

        $this->authorizeStage(request()->user(), $lowongan, $stage);

        DB::transaction(function () use ($stage): void {
            $stage->update(['status' => ApplicationStageStatus::Aktif->value]);
            InterviewResult::where('application_stage_id', $stage->id)->update(['is_superseded' => true]);
        });

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Pelamar dilanjutkan. Form wawancara siap diisi kembali.');
    }

    public function startOver(Vacancy $lowongan, Application $application): RedirectResponse
    {
        abort_if($application->vacancy_id !== $lowongan->id, 404);

        $stage = $application->stages()
            ->whereIn('status', [ApplicationStageStatus::Reserved->value, ApplicationStageStatus::Gagal->value])
            ->orderBy('position')
            ->first();

        if (! $stage) {
            return back()->withErrors(['restart' => 'Hanya kandidat dengan status Tunda atau Gagal yang dapat diulang.']);
        }

        $this->authorizeStage(request()->user(), $lowongan, $stage);

        DB::transaction(function () use ($application): void {
            $stages = $application->stages()->orderBy('position')->get();

            $stages->values()->each(function ($stage, $index): void {
                $stage->update([
                    'status' => match ($index) {
                        0 => ApplicationStageStatus::Selesai->value,
                        1 => ApplicationStageStatus::Aktif->value,
                        default => ApplicationStageStatus::Pending->value,
                    },
                ]);
            });

            InterviewResult::whereIn('application_stage_id', $stages->pluck('id'))->update(['is_superseded' => true]);
        });

        return redirect()
            ->route('lowongan.pipeline', $lowongan)
            ->with('success', 'Proses dimulai ulang dari screening.');
    }

    private function authorizeStage($user, Vacancy $lowongan, ApplicationStage $stage): void
    {
        abort_unless($user->hasPermission(Permissions::VACANCY_VIEW), 403);

        if (! $user->hasPermission(Permissions::VACANCY_VIEW_ORG)) {
            abort_unless($user->isInUnit($lowongan->unit_id), 403);
        }
    }
}
