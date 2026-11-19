<?php

namespace App\Models;

use Database\Factories\MbtiSubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MbtiSubmission extends Model
{
    /** @use HasFactory<MbtiSubmissionFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'token',
        'link_tes',
        'catatan',
        'sent_at',
        'proof_paths',
        'proof_uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'proof_paths' => 'array',
            'proof_uploaded_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function hasProof(): bool
    {
        return ! empty($this->proof_paths);
    }
}
