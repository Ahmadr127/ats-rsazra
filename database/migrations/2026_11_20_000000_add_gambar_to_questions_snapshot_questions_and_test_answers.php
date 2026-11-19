<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('gambar_path')->nullable()->after('pertanyaan');
        });

        Schema::table('vacancy_test_snapshot_questions', function (Blueprint $table) {
            $table->string('gambar_path')->nullable()->after('pertanyaan');
        });

        Schema::table('test_answers', function (Blueprint $table) {
            $table->string('gambar_jawaban_path')->nullable()->after('jawaban_teks');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('gambar_path');
        });

        Schema::table('vacancy_test_snapshot_questions', function (Blueprint $table) {
            $table->dropColumn('gambar_path');
        });

        Schema::table('test_answers', function (Blueprint $table) {
            $table->dropColumn('gambar_jawaban_path');
        });
    }
};
