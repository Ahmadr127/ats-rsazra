<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        DB::table('site_settings')->insert([
            [
                'key' => 'hero_eyebrow',
                'value' => 'RS Azra · Karier',
                'label' => 'Teks kecil hero',
                'description' => 'Baris kecil di atas judul halaman karier.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_title',
                'value' => 'Bergabung dalam karya penyembuhan yang bermakna.',
                'label' => 'Judul hero',
                'description' => 'Judul besar halaman karier.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'hero_lede',
                'value' => 'Jelajahi {jumlah_posisi} posisi terbuka di {jumlah_unit} unit RS Azra, termasuk {lowongan_terbaru}. Pilih lowongan, baca detailnya, dan lamar langsung dari halaman ini.',
                'label' => 'Paragraf hero',
                'description' => 'Mendukung token dinamis: {jumlah_posisi}, {jumlah_unit}, {lowongan_terbaru}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
