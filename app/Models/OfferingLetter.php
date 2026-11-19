<?php

namespace App\Models;

use App\Enums\OfferingLetterStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class OfferingLetter extends Model
{
    protected $fillable = [
        'application_id',
        'jabatan_ditawarkan',
        'gaji',
        'tanggal_mulai',
        'catatan',
        'surat_path',
        'surat_ttd_path',
        'sent_at',
        'status',
        'responded_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'sent_at' => 'datetime',
            'status' => OfferingLetterStatus::class,
            'responded_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function isPending(): bool
    {
        return $this->status === OfferingLetterStatus::Pending;
    }

    public function isResponded(): bool
    {
        return $this->status !== OfferingLetterStatus::Pending;
    }

    public function suratUrl(): ?string
    {
        return $this->surat_path ? Storage::disk('public')->url($this->surat_path) : null;
    }

    public function suratTtdUrl(): ?string
    {
        return $this->surat_ttd_path ? Storage::disk('public')->url($this->surat_ttd_path) : null;
    }
}
