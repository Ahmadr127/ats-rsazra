<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\QuestionType;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Question;
use App\Models\QuestionBankTemplate;
use App\Models\QuestionOption;
use App\Models\Stage;
use App\Models\TestAnswer;
use App\Models\TestSubmission;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class QuestionImageTest extends TestCase
{
    use RefreshDatabase;

    private ?string $testDiskRoot = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testDiskRoot = storage_path('framework/testing/disks/public-'.Str::random(8));
        Storage::fake('public');
        config()->set('filesystems.disks.public.root', $this->testDiskRoot);
        Storage::forgetDisk('public');
        File::ensureDirectoryExists($this->testDiskRoot);
    }

    protected function tearDown(): void
    {
        if ($this->testDiskRoot !== null) {
            File::deleteDirectory($this->testDiskRoot);
        }

        parent::tearDown();
    }

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    private function hrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    /**
     * @param  list<string>  $types
     * @return array<string, mixed>
     */
    private function createVacancyWithTest(Unit $unit, array $types, bool $withImages = false): array
    {
        $stageKeys = ['lamaran', 'tes_kompetensi', 'onboarding'];
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        $vacancy = Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);

        $questionBankTemplate = QuestionBankTemplate::factory()->create();
        $questions = collect();

        foreach ($types as $index => $type) {
            $question = Question::factory()->create([
                'question_bank_template_id' => $questionBankTemplate->id,
                'tipe' => $type,
                'nilai_poin' => 10,
                'urutan' => $index + 1,
            ]);

            if ($withImages) {
                $path = "question-images/{$questionBankTemplate->id}/{$question->id}-source.jpg";
                Storage::disk('public')->put($path, 'source-image');
                $question->update(['gambar_path' => $path]);
            }

            if ($type === QuestionType::Mc->value) {
                QuestionOption::factory()->correct()->create(['question_id' => $question->id]);
                QuestionOption::factory()->create(['question_id' => $question->id]);
            }

            $questions->push($question);
        }

        $vacancyTest = VacancyTest::create(['vacancy_id' => $vacancy->id, 'batas_waktu_menit' => 60]);
        foreach ($questions as $index => $question) {
            $vacancyTest->questions()->attach($question->id, ['urutan' => $index + 1]);
        }

        $testSnapshot = VacancyTestSnapshot::createFromVacancyTest($vacancyTest->fresh());

        return compact('vacancy', 'vacancyTest', 'testSnapshot', 'questionBankTemplate', 'questions');
    }

    private function makeApplicationAtTestStage(Vacancy $vacancy): Application
    {
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();
        foreach ($stages as $index => $stage) {
            ApplicationStage::create([
                'application_id' => $application->id,
                'position' => $index + 1,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => match ($stage->key) {
                    'lamaran' => ApplicationStageStatus::Selesai,
                    'tes_kompetensi' => ApplicationStageStatus::Aktif,
                    default => ApplicationStageStatus::Pending,
                },
            ]);
        }

        return $application;
    }

    private function makeSubmission(Vacancy $vacancy, VacancyTestSnapshot $snapshot, bool $submitted = false, int $startedMinutesAgo = 5): TestSubmission
    {
        $application = $this->makeApplicationAtTestStage($vacancy);

        return TestSubmission::factory()->create([
            'application_id' => $application->id,
            'vacancy_test_snapshot_id' => $snapshot->id,
            'started_at' => now()->subMinutes($startedMinutesAgo),
            ...($submitted ? ['submitted_at' => now()->subMinute()] : []),
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function questionPayload(string $type, array $overrides = []): array
    {
        return array_merge([
            'tipe' => $type,
            'pertanyaan' => 'Pertanyaan '.Str::random(5),
            'nilai_poin' => 10,
        ], $overrides);
    }

    // ===== Question bank: image upload =====

    public function test_hr_admin_can_create_image_question_without_options(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes Unggah Gambar',
            'questions' => [$this->questionPayload('image')],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $this->assertDatabaseHas('questions', ['tipe' => 'image']);
        $this->assertDatabaseCount('question_options', 0);
    }

    public function test_question_image_is_stored_on_create(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $response = $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes Bergambar',
            'questions' => [
                $this->questionPayload('mc', [
                    'gambar' => UploadedFile::fake()->image('soal.jpg', 120, 120),
                    'options' => [
                        ['teks_opsi' => 'A'],
                        ['teks_opsi' => 'B'],
                    ],
                    'correct_option' => 0,
                ]),
            ],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $question = Question::firstOrFail();
        $this->assertNotNull($question->gambar_path);
        $this->assertStringStartsWith('question-images/', $question->gambar_path);
        Storage::disk('public')->assertExists($question->gambar_path);
    }

    public function test_question_image_validation_rejects_non_image_and_oversized_file(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();

        $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes File Salah',
            'questions' => [
                $this->questionPayload('essay', [
                    'gambar' => UploadedFile::fake()->create('soal.pdf', 100, 'application/pdf'),
                ]),
            ],
        ])->assertSessionHasErrors('questions.0.gambar');

        $this->actingAs($admin)->post(route('template-bank-soal.store'), [
            'nama' => 'Tes File Besar',
            'questions' => [
                $this->questionPayload('essay', [
                    'gambar' => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(6000),
                ]),
            ],
        ])->assertSessionHasErrors('questions.0.gambar');

        $this->assertDatabaseCount('question_bank_templates', 0);
    }

    public function test_updating_question_replaces_image_and_deletes_old_file(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = QuestionBankTemplate::factory()->create(['nama' => 'Lama']);
        $question = Question::factory()->create([
            'question_bank_template_id' => $template->id,
            'tipe' => QuestionType::Essay->value,
            'pertanyaan' => 'Pertanyaan lama',
            'nilai_poin' => 10,
            'urutan' => 1,
        ]);
        $oldPath = "question-images/{$template->id}/{$question->id}-old.jpg";
        Storage::disk('public')->put($oldPath, 'old');
        $question->update(['gambar_path' => $oldPath]);

        $response = $this->actingAs($admin)->put(route('template-bank-soal.update', $template), [
            'nama' => 'Baru',
            'questions' => [
                [
                    'id' => $question->id,
                    'tipe' => 'essay',
                    'pertanyaan' => 'Pertanyaan baru',
                    'nilai_poin' => 15,
                    'gambar' => UploadedFile::fake()->image('baru.png', 120, 120),
                ],
            ],
        ]);

        $response->assertRedirect(route('template-bank-soal.index'));
        $question->refresh();
        $this->assertNotEquals($oldPath, $question->gambar_path);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($question->gambar_path);
    }

    public function test_removing_question_image_deletes_file_and_nulls_column(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = QuestionBankTemplate::factory()->create();
        $question = Question::factory()->create([
            'question_bank_template_id' => $template->id,
            'tipe' => QuestionType::Essay->value,
            'urutan' => 1,
        ]);
        $oldPath = "question-images/{$template->id}/{$question->id}-old.jpg";
        Storage::disk('public')->put($oldPath, 'old');
        $question->update(['gambar_path' => $oldPath]);

        $this->actingAs($admin)->put(route('template-bank-soal.update', $template), [
            'nama' => $template->nama,
            'questions' => [[
                'id' => $question->id,
                'tipe' => 'essay',
                'pertanyaan' => $question->pertanyaan,
                'nilai_poin' => $question->nilai_poin,
                'hapus_gambar' => '1',
            ]],
        ])->assertRedirect(route('template-bank-soal.index'));

        $this->assertNull($question->refresh()->gambar_path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_removed_question_image_is_deleted_on_update(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = QuestionBankTemplate::factory()->create();
        $kept = Question::factory()->create([
            'question_bank_template_id' => $template->id,
            'tipe' => QuestionType::Essay->value,
            'urutan' => 1,
        ]);
        $removed = Question::factory()->create([
            'question_bank_template_id' => $template->id,
            'tipe' => QuestionType::Essay->value,
            'urutan' => 2,
        ]);
        $removedPath = "question-images/{$template->id}/{$removed->id}-old.jpg";
        Storage::disk('public')->put($removedPath, 'old');
        $removed->update(['gambar_path' => $removedPath]);

        $this->actingAs($admin)->put(route('template-bank-soal.update', $template), [
            'nama' => $template->nama,
            'questions' => [[
                'id' => $kept->id,
                'tipe' => 'essay',
                'pertanyaan' => $kept->pertanyaan,
                'nilai_poin' => $kept->nilai_poin,
            ]],
        ])->assertRedirect(route('template-bank-soal.index'));

        Storage::disk('public')->assertMissing($removedPath);
        $this->assertDatabaseMissing('questions', ['id' => $removed->id]);
    }

    public function test_deleting_template_deletes_its_images(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        $template = QuestionBankTemplate::factory()->create();
        $question = Question::factory()->create(['question_bank_template_id' => $template->id, 'urutan' => 1]);
        $path = "question-images/{$template->id}/{$question->id}-old.jpg";
        Storage::disk('public')->put($path, 'old');
        $question->update(['gambar_path' => $path]);

        $this->actingAs($admin)->delete(route('template-bank-soal.destroy', $template))
            ->assertRedirect(route('template-bank-soal.index'));

        Storage::disk('public')->assertMissing($path);
    }

    // ===== Snapshot =====

    public function test_snapshot_copies_question_image(): void
    {
        $this->seedStages();
        ['testSnapshot' => $snapshot, 'questions' => $questions] = $this->createVacancyWithTest(Unit::factory()->create(), ['image', 'mc'], true);

        $snapshotQuestion = $snapshot->questions()->where('tipe', QuestionType::Image)->firstOrFail();

        $this->assertNotNull($snapshotQuestion->gambar_path);
        $this->assertNotEquals($questions->first()->gambar_path, $snapshotQuestion->gambar_path);
        Storage::disk('public')->assertExists($snapshotQuestion->gambar_path);
    }

    public function test_snapshot_image_survives_source_question_deletion(): void
    {
        $this->seedStages();
        ['testSnapshot' => $snapshot, 'questionBankTemplate' => $template] = $this->createVacancyWithTest(Unit::factory()->create(), ['image'], true);

        $snapshotQuestion = $snapshot->questions()->firstOrFail();
        $snapshotPath = $snapshotQuestion->gambar_path;

        $template->delete();

        Storage::disk('public')->assertExists($snapshotPath);
        $this->assertDatabaseHas('vacancy_test_snapshot_questions', ['id' => $snapshotQuestion->id, 'gambar_path' => $snapshotPath]);
    }

    // ===== Candidate test taking =====

    public function test_candidate_page_renders_question_image_and_file_input(): void
    {
        $this->seedStages();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image'], true);
        $submission = $this->makeSubmission($vacancy, $snapshot);
        $question = $snapshot->questions()->firstOrFail();

        $response = $this->get(route('tes.show', $submission->token));

        $response->assertOk();
        $response->assertSee('answers['.$question->id.']', false);
        $response->assertSee('type="file"', false);
        $response->assertSee(Storage::disk('public')->url($question->gambar_path), false);
        $response->assertSee('enctype="multipart/form-data"', false);
    }

    public function test_submitting_image_answer_stores_file_and_defers_score(): void
    {
        $this->seedStages();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['mc', 'image']);
        $submission = $this->makeSubmission($vacancy, $snapshot);
        $questions = $snapshot->questions;
        $mcQuestion = $questions->firstWhere('tipe', QuestionType::Mc);
        $imageQuestion = $questions->firstWhere('tipe', QuestionType::Image);

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [
                $mcQuestion->id => $mcQuestion->options->firstWhere('is_correct', true)->id,
                $imageQuestion->id => UploadedFile::fake()->image('jawaban.jpg', 120, 120),
            ],
        ])->assertRedirect(route('tes.show', $submission->token));

        $answer = $submission->answers()->where('vacancy_test_snapshot_question_id', $imageQuestion->id)->firstOrFail();
        $this->assertNotNull($answer->gambar_jawaban_path);
        Storage::disk('public')->assertExists($answer->gambar_jawaban_path);
        $this->assertNull($answer->skor);
        $this->assertFalse($answer->is_reviewed);
        $this->assertNull($submission->refresh()->total_skor);
    }

    public function test_image_answer_is_required_on_submit(): void
    {
        $this->seedStages();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image']);
        $submission = $this->makeSubmission($vacancy, $snapshot);
        $questionId = $snapshot->questions()->firstOrFail()->id;

        $this->post(route('tes.submit', $submission->token), ['answers' => []])
            ->assertSessionHasErrors("answers.{$questionId}");

        $this->assertDatabaseCount('test_answers', 0);
    }

    public function test_invalid_image_answer_is_rejected(): void
    {
        $this->seedStages();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image']);
        $submission = $this->makeSubmission($vacancy, $snapshot);
        $questionId = $snapshot->questions()->firstOrFail()->id;

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [$questionId => UploadedFile::fake()->image('besar.jpg', 100, 100)->size(6000)],
        ])->assertSessionHasErrors("answers.{$questionId}");

        $this->post(route('tes.submit', $submission->token), [
            'answers' => [$questionId => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf')],
        ])->assertSessionHasErrors("answers.{$questionId}");

        $this->assertDatabaseCount('test_answers', 0);
        $this->assertEmpty(Storage::disk('public')->files("test-answer-images/{$submission->application_id}"));
    }

    public function test_expired_submission_auto_submits_image_answer_without_file(): void
    {
        $this->seedStages();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image']);
        $submission = $this->makeSubmission($vacancy, $snapshot, false, 70);

        $this->get(route('tes.show', $submission->token))->assertRedirect(route('tes.show', $submission->token));

        $answer = TestAnswer::firstOrFail();
        $this->assertNull($answer->gambar_jawaban_path);
        $this->assertFalse($answer->is_reviewed);
        $this->assertNotNull($submission->refresh()->submitted_at);
    }

    // ===== Review =====

    public function test_staff_can_score_image_answer_and_total_is_computed(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['mc', 'image']);
        $submission = $this->makeSubmission($vacancy, $snapshot, true);
        $questions = $snapshot->questions;
        $mcQuestion = $questions->firstWhere('tipe', QuestionType::Mc);
        $imageQuestion = $questions->firstWhere('tipe', QuestionType::Image);

        $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $mcQuestion->id,
            'skor' => 10,
            'is_reviewed' => true,
        ]);
        $imageAnswer = $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $imageQuestion->id,
            'gambar_jawaban_path' => 'test-answer-images/1/jawaban.jpg',
            'is_reviewed' => false,
        ]);
        $submission->update(['total_skor' => null]);

        $this->actingAs($admin)->post(route('lowongan.tes.ulasan.skor', [$vacancy, $imageAnswer]), ['skor' => 8])
            ->assertRedirect();

        $this->assertDatabaseHas('test_answers', ['id' => $imageAnswer->id, 'skor' => 8, 'is_reviewed' => true]);
        $this->assertEquals(18, $submission->refresh()->total_skor);
    }

    public function test_staff_cannot_score_mc_answer_via_manual_score_route(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['mc']);
        $submission = $this->makeSubmission($vacancy, $snapshot, true);
        $answer = $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $snapshot->questions()->firstOrFail()->id,
            'skor' => 10,
            'is_reviewed' => true,
        ]);

        $this->actingAs($admin)->post(route('lowongan.tes.ulasan.skor', [$vacancy, $answer]), ['skor' => 5])
            ->assertStatus(422);
    }

    public function test_review_page_shows_question_and_answer_images(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image'], true);
        $submission = $this->makeSubmission($vacancy, $snapshot, true);
        $question = $snapshot->questions()->firstOrFail();
        $answerPath = 'test-answer-images/'.$submission->application_id.'/jawaban.jpg';
        Storage::disk('public')->put($answerPath, 'answer');
        $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $question->id,
            'gambar_jawaban_path' => $answerPath,
            'is_reviewed' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('lowongan.pipeline.show', [$vacancy, $submission->application]));

        $response->assertOk();
        $response->assertSee(Storage::disk('public')->url($question->gambar_path), false);
        $response->assertSee(Storage::disk('public')->url($answerPath), false);
    }

    public function test_pdf_export_with_image_answer_succeeds(): void
    {
        $this->seedStages();
        $admin = $this->hrAdmin();
        ['vacancy' => $vacancy, 'testSnapshot' => $snapshot] = $this->createVacancyWithTest(Unit::factory()->create(), ['image']);
        $submission = $this->makeSubmission($vacancy, $snapshot, true);
        $question = $snapshot->questions()->firstOrFail();
        Storage::disk('public')->put('test-answer-images/1/jawaban.jpg', 'answer');
        $submission->answers()->create([
            'vacancy_test_snapshot_question_id' => $question->id,
            'gambar_jawaban_path' => 'test-answer-images/1/jawaban.jpg',
            'skor' => 10,
            'is_reviewed' => true,
        ]);
        $submission->update(['total_skor' => 10]);

        $response = $this->actingAs($admin)->get(route('lowongan.kandidat.pdf', [$vacancy, $submission->application]));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
