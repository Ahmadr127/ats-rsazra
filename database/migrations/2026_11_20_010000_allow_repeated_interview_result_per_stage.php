<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interview_results', function (Blueprint $table): void {
            $table->dropUnique(['application_stage_id']);
            $table->boolean('is_superseded')->default(false)->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('interview_results', function (Blueprint $table): void {
            $table->unique(['application_stage_id']);
            $table->dropColumn('is_superseded');
        });
    }
};
