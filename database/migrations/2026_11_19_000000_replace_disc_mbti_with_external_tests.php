<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the internal DISC/MBTI question engine with external test links,
     * candidate-uploaded proof images, and email delivery metadata.
     */
    public function up(): void
    {
        // Drop children before parents because these tables use foreign keys.
        Schema::dropIfExists('disc_answers');
        Schema::dropIfExists('disc_results');
        Schema::dropIfExists('disc_question_words');
        Schema::dropIfExists('disc_questions');
        Schema::dropIfExists('disc_submissions');

        Schema::dropIfExists('mbti_answers');
        Schema::dropIfExists('mbti_results');
        Schema::dropIfExists('mbti_questions');
        Schema::dropIfExists('mbti_submissions');

        Schema::create('disc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->text('link_tes')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('proof_paths')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mbti_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->text('link_tes')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('proof_paths')->nullable();
            $table->timestamp('proof_uploaded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Restore the legacy schema for rollback. This intentionally recreates
     * empty legacy tables because the old internal test data is discarded.
     */
    public function down(): void
    {
        Schema::dropIfExists('disc_submissions');
        Schema::dropIfExists('mbti_submissions');

        Schema::create('disc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('disc_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('urutan')->unique();
            $table->timestamps();
        });

        Schema::create('disc_question_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_question_id')->constrained()->cascadeOnDelete();
            $table->string('teks');
            $table->string('dimensi');
            $table->timestamps();
        });

        Schema::create('disc_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('disc_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('most_disc_word_id')->constrained('disc_question_words')->cascadeOnDelete();
            $table->foreignId('least_disc_word_id')->constrained('disc_question_words')->cascadeOnDelete();
            $table->unique(['disc_submission_id', 'disc_question_id']);
            $table->timestamps();
        });

        Schema::create('disc_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disc_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('skor_d');
            $table->unsignedTinyInteger('skor_i');
            $table->unsignedTinyInteger('skor_s');
            $table->unsignedTinyInteger('skor_c');
            $table->string('tipe_primer');
            $table->string('tipe_sekunder');
            $table->timestamps();
        });

        Schema::create('mbti_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mbti_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('urutan')->unique();
            $table->string('dikotomi', 2);
            $table->text('pernyataan_a');
            $table->string('kutub_a', 1);
            $table->text('pernyataan_b');
            $table->timestamps();
        });

        Schema::create('mbti_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mbti_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mbti_question_id')->constrained()->cascadeOnDelete();
            $table->string('pilihan', 1);
            $table->unique(['mbti_submission_id', 'mbti_question_id']);
            $table->timestamps();
        });

        Schema::create('mbti_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mbti_submission_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('skor_e')->default(0);
            $table->unsignedSmallInteger('skor_i')->default(0);
            $table->unsignedSmallInteger('skor_s')->default(0);
            $table->unsignedSmallInteger('skor_n')->default(0);
            $table->unsignedSmallInteger('skor_t')->default(0);
            $table->unsignedSmallInteger('skor_f')->default(0);
            $table->unsignedSmallInteger('skor_j')->default(0);
            $table->unsignedSmallInteger('skor_p')->default(0);
            $table->string('tipe', 4);
            $table->unsignedSmallInteger('kekuatan_ei')->default(0);
            $table->unsignedSmallInteger('kekuatan_sn')->default(0);
            $table->unsignedSmallInteger('kekuatan_tf')->default(0);
            $table->unsignedSmallInteger('kekuatan_jp')->default(0);
            $table->timestamps();
        });
    }
};
