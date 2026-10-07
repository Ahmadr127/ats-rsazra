<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Manual RBAC permission. Roles are records (App\Models\Role),
 * linked through the permission_role pivot — no package.
 */
class Permission extends Model
{
    protected $fillable = [
        'key',
        'label',
        'group',
    ];

    /**
     * Role IDs currently granted a permission key.
     *
     * @return list<int>
     */
    public static function roleIdsWith(string $key): array
    {
        return DB::table('permission_role')
            ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
            ->where('permissions.key', $key)
            ->pluck('permission_role.role_id')
            ->all();
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id');
    }
}
