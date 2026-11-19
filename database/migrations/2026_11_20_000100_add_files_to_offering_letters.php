<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offering_letters', function (Blueprint $table) {
            $table->string('surat_path')->nullable()->after('catatan');
            $table->string('surat_ttd_path')->nullable()->after('surat_path');
        });
    }

    public function down(): void
    {
        Schema::table('offering_letters', function (Blueprint $table) {
            $table->dropColumn(['surat_path', 'surat_ttd_path']);
        });
    }
};
