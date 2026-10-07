<?php

namespace App\Support;

use App\Models\User;

/**
 * Single place that maps interview stage keys to decide permissions.
 * Replaces the role hardcodes previously duplicated in InterviewController
 * and StoreInterviewResultRequest.
 */
final class InterviewStageMap
{
    public const USER = 'wawancara_user';

    public const MANAGER = 'wawancara_manajer_hr';

    public const DIRECTOR = 'wawancara_direktur';

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return [self::USER, self::MANAGER, self::DIRECTOR];
    }

    public static function decidePermissionForStage(string $stageKey): ?string
    {
        return match ($stageKey) {
            self::USER => Permissions::INTERVIEW_DECIDE_USER,
            self::MANAGER => Permissions::INTERVIEW_DECIDE_MANAGER,
            self::DIRECTOR => Permissions::INTERVIEW_DECIDE_DIRECTOR,
            default => null,
        };
    }

    /**
     * Which interview stage the user may decide, based on granted
     * decide permissions (manager first, then director, then user).
     */
    public static function stageKeyForDecider(User $user): ?string
    {
        if ($user->hasPermission(Permissions::INTERVIEW_DECIDE_MANAGER)) {
            return self::MANAGER;
        }

        if ($user->hasPermission(Permissions::INTERVIEW_DECIDE_DIRECTOR)) {
            return self::DIRECTOR;
        }

        if ($user->hasPermission(Permissions::INTERVIEW_DECIDE_USER)) {
            return self::USER;
        }

        return null;
    }
}
