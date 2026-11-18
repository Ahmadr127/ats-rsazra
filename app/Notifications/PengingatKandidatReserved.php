<?php

namespace App\Notifications;

use App\Models\Vacancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PengingatKandidatReserved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Vacancy $vacancy) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'vacancy_id' => $this->vacancy->id,
            'judul_posisi' => $this->vacancy->judul_posisi,
            'tenggat_lamaran' => $this->vacancy->tenggat_lamaran->toDateString(),
        ];
    }
}
