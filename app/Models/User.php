<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role_id',
        'must_change_password',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (self $user): void {
            if ($user->isDirty('role_id')) {
                self::flushPermissionCache();
            }
        });
    }

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @var array<int, array<string, true>> */
    private static array $permissionCache = [];

    public static function flushPermissionCache(): void
    {
        self::$permissionCache = [];
    }

    /**
     * @return array<string, true>
     */
    public function permissionKeys(): array
    {
        $userId = $this->getKey();

        if (! isset(self::$permissionCache[$userId])) {
            $keys = $this->role_id === null ? [] : DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $this->role_id)
                ->pluck('permissions.key')
                ->all();

            self::$permissionCache[$userId] = array_fill_keys($keys, true);
        }

        return self::$permissionCache[$userId];
    }

    public function hasPermission(string $key): bool
    {
        return isset($this->permissionKeys()[$key]);
    }

    /**
     * @param  list<string>  $keys
     */
    public function hasAnyPermission(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->hasPermission($key)) {
                return true;
            }
        }

        return false;
    }

    public function requirePermission(string $key): void
    {
        abort_unless($this->hasPermission($key), 403, 'Anda tidak memiliki akses ke halaman ini.');
    }

    public function isInUnit(?int $unitId): bool
    {
        return $unitId !== null && $this->employee?->unit_id === $unitId;
    }

    /**
     * Active users whose role is granted the permission (notification recipients).
     *
     * @return Collection<int, self>
     */
    public static function withPermission(string $key): Collection
    {
        $roleIds = Permission::roleIdsWith($key);

        if ($roleIds === []) {
            return new Collection;
        }

        return self::query()->where('is_active', true)->whereIn('role_id', $roleIds)->get();
    }

    public function scopeWithPermission(Builder $query, string $key): void
    {
        $roleIds = Permission::roleIdsWith($key);

        $query->whereIn('role_id', $roleIds === [] ? [-1] : $roleIds);
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
