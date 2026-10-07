<?php

namespace App\Policies;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Permissions;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(Permissions::MENU_TAMPILAN);
    }

    public function update(User $user, SiteSetting $siteSetting): bool
    {
        return $user->hasPermission(Permissions::MENU_TAMPILAN);
    }
}
