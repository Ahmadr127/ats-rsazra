<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'key' => 'seo_title',
                'value' => 'Lowongan Kerja RS Azra - {jumlah_posisi} Posisi Terbuka',
                'label' => 'Judul SEO',
                'description' => 'Judul tab browser dan hasil pencarian. Mendukung token dinamis.',
            ],
            [
                'key' => 'seo_description',
                'value' => 'Temukan {jumlah_posisi} lowongan kerja terbaru di RS Azra Bogor dari {jumlah_unit} unit, termasuk {lowongan_terbaru}. Lamar online.',
                'label' => 'Deskripsi SEO',
                'description' => 'Meta description halaman karier. Mendukung token dinamis. Rekomendasi 150-160 karakter.',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['created_at' => now(), 'updated_at' => now()]
            );
        }

        Cache::forget('site_settings');
    }

    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'seo_title',
            'seo_description',
        ])->delete();

        Cache::forget('site_settings');
    }
};
