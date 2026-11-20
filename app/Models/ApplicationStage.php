<?php

namespace App\Models;

use App\Enums\ApplicationStageStatus;
use App\Support\InterviewStageMap;
use Database\Factories\ApplicationStageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApplicationStage extends Model
{
    /** @use HasFactory<ApplicationStageFactory> */
    use HasFactory;

    protected $fillable = [
        'application_id',
        'position',
        'key',
        'nama',
        'status',
        'catatan',
        'jadwal',
        'lokasi',
        'reviewed_by',
        'interviewer_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStageStatus::class,
            'position' => 'integer',
            'jadwal' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function interviewResult(): HasOne
    {
        return $this->hasOne(InterviewResult::class);
    }

    public function activeInterviewResult(): HasOne
    {
        return $this->hasOne(InterviewResult::class)->where('is_superseded', false);
    }

    public function isInterviewStage(): bool
    {
        return in_array($this->key, InterviewStageMap::keys(), true);
    }

    public function hasInterviewResult(): bool
    {
        if ($this->relationLoaded('activeInterviewResult')) {
            return $this->activeInterviewResult !== null;
        }

        return $this->activeInterviewResult()->exists();
    }

    public function nextStage(): ?ApplicationStage
    {
        return static::query()
            ->where('application_id', $this->application_id)
            ->where('position', '>', $this->position)
            ->orderBy('position')
            ->first();
    }

    /**
     * A stage counts as "filled" once it has a decision. Pending/Aktif stages
     * are still open, so the previous interview form stays editable.
     */
    public function isNextStageFilled(): bool
    {
        $next = $this->nextStage();

        if (! $next) {
            return true;
        }

        if (in_array($next->status, [ApplicationStageStatus::Pending, ApplicationStageStatus::Aktif], true)) {
            return $next->hasInterviewResult();
        }

        return true;
    }

    public function isInterviewEditable(): bool
    {
        return $this->isInterviewStage()
            && $this->hasInterviewResult()
            && ! $this->isNextStageFilled();
    }

    /**
     * A recorded "lulus" decision can still be corrected to "gagal" or
     * "reserved" while the next stage has not been filled. Reserved/Gagal
     * results are corrected through the resume callback instead.
     */
    public function canChangeInterviewDecision(): bool
    {
        if (! $this->isInterviewStage() || ! $this->hasInterviewResult()) {
            return false;
        }

        $result = $this->relationLoaded('activeInterviewResult')
            ? $this->activeInterviewResult
            : $this->activeInterviewResult()->first();

        if (! $result || $result->keputusan !== 'lulus') {
            return false;
        }

        return ! $this->isNextStageFilled();
    }

    public function mcuResult(): HasOne
    {
        return $this->hasOne(McuResult::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}
