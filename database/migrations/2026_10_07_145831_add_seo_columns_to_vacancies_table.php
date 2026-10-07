<?php

use App\Models\Vacancy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('judul_posisi');
            $table->string('meta_description', 160)->nullable()->after('kualifikasi');
        });

        // Backfill slug unik untuk baris yang sudah ada.
        Vacancy::query()->orderBy('id')->chunkById(100, function ($vacancies) {
            foreach ($vacancies as $vacancy) {
                $base = Str::slug($vacancy->judul_posisi) ?: 'lowongan';
                $vacancy->slug = $base.'-'.$vacancy->id;
                $vacancy->saveQuietly();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'meta_description']);
        });
    }
};
