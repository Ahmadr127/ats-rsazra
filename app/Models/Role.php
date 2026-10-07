<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Access role as data (managed via CRUD), not code.
 * The `key` is immutable once created; permissions attach to records.
 */
class Role extends Model
{
    public const HrAdmin = 'hr_admin';

    public const HrManager = 'hr_manager';

    public const UnitHead = 'unit_head';

    public const Director = 'director';

    public const Employee = 'employee';

    protected $fillable = [
        'key',
        'label',
    ];

    /**
     * @return list<string>
     */
    public static function defaultKeys(): array
    {
        return [self::HrAdmin, self::HrManager, self::UnitHead, self::Director, self::Employee];
    }

    public static function defaultLabel(string $key): string
    {
        return match ($key) {
            self::HrAdmin => 'Admin HR',
            self::HrManager => 'Manajer HR',
            self::UnitHead => 'Kepala Unit',
            self::Director => 'Direktur',
            self::Employee => 'Karyawan',
            default => $key,
        };
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }
}
