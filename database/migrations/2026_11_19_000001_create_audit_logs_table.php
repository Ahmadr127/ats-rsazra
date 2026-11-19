<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index();
            $table->string('event', 255)->index();
            $table->string('status', 50)->nullable()->index();
            $table->string('severity', 20)->default('info')->index();
            $table->string('auditable_type', 191)->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('subject', 500)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable()->index();
            $table->string('actor_name', 191)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('request_id', 191)->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('url', 500)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('context')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['category', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
