<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
    ];

    /**
     * Ambil nilai pengaturan. Hilang → default agar halaman publik
     * tidak pernah 500 walau baris belum ada.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('site_settings', fn () => static::pluck('value', 'key')->all());

        return $all[$key] ?? $default;
    }

    public static function flush(): void
    {
        Cache::forget('site_settings');
    }
}
