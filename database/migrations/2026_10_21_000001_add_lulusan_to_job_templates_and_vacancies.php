<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_templates', function (Blueprint $table) {
            $table->string('lulusan')->nullable()->after('jenis_pekerjaan');
        });

        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('lulusan')->nullable()->after('jenis_pekerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn('lulusan');
        });

        Schema::table('job_templates', function (Blueprint $table) {
            $table->dropColumn('lulusan');
        });
    }
};
