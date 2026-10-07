<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $rows = [
            [
                'key' => 'kontak_telepon',
                'value' => '(0251) 8382417',
                'label' => 'Nomor telepon',
                'description' => 'Tampil di bilah atas dan footer. Link tel: dibuat otomatis dari angka.',
            ],
            [
                'key' => 'kontak_wa_nomor',
                'value' => '6281219801997',
                'label' => 'Nomor WhatsApp (format internasional tanpa +)',
                'description' => 'Untuk tautan wa.me. Contoh: 6281219801997.',
            ],
            [
                'key' => 'kontak_wa_label',
                'value' => 'WA 0812 1980 1997',
                'label' => 'Teks WhatsApp',
                'description' => 'Teks tombol WhatsApp di bilah atas.',
            ],
            [
                'key' => 'kontak_email',
                'value' => 'rsazra@gmail.com',
                'label' => 'Email',
                'description' => 'Tampil di footer halaman karier.',
            ],
            [
                'key' => 'kontak_alamat',
                'value' => 'Jl. Pintu Air No.1, Sempur, Bogor Tengah, Kota Bogor 16112',
                'label' => 'Alamat',
                'description' => 'Tampil di footer halaman karier.',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('site_settings')->updateOrInsert(
                ['key' => $row['key']],
                $row + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('site_settings')->whereIn('key', [
            'kontak_telepon',
            'kontak_wa_nomor',
            'kontak_wa_label',
            'kontak_email',
            'kontak_alamat',
        ])->delete();
    }
};
