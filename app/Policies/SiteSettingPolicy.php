<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\SiteSetting;
use App\Models\User;

class SiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }

    public function update(User $user, SiteSetting $siteSetting): bool
    {
        return $user->hasRole(Role::HrAdmin);
    }
}
