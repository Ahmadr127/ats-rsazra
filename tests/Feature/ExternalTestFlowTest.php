<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\DiscSubmission;
use App\Models\MbtiSubmission;
use App\Models\Role;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExternalTestFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    /** @param list<string> $stageKeys */
    private function createVacancy(array $stageKeys): Vacancy
    {
        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => Unit::factory()->create()->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    private function makeApplicationAt(Vacancy $vacancy, string $targetKey): Application
    {
        $vacancy->loadMissing('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'token' => Str::uuid()->toString(),
        ]);

        $stages = $vacancy->workflowTemplateSnapshot->stages()->orderBy('position')->get();
        $targetIndex = $stages->search(fn ($stage) => $stage->key === $targetKey);

        foreach ($stages as $index => $stage) {
            $status = match (true) {
                $index < $targetIndex => ApplicationStageStatus::Selesai,
                $index === $targetIndex => ApplicationStageStatus::Aktif,
                default => ApplicationStageStatus::Pending,
            };

            ApplicationStage::create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    private function hrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    // ===== Send link =====

    public function test_admin_hr_can_send_disc_link_and_email_contains_upload_page(): void
    {
        Mail::fake();

        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
        ]);

        $response = $this->actingAs($this->hrAdmin())->post(
            route('lowongan.lamaran.tes-disc.kirim', [$vacancy, $application]),
            [
                'link_tes' => 'https://disc.example.test/asesmen/abc',
                'catatan' => 'Kerjakan tes pada pagi hari.',
            ],
        );

        $response->assertSessionHasNoErrors();

        $submission->refresh();
        $this->assertSame('https://disc.example.test/asesmen/abc', $submission->link_tes);
        $this->assertSame('Kerjakan tes pada pagi hari.', $submission->catatan);
        $this->assertNotNull($submission->sent_at);

        Mail::assertQueued(TemplatedMail::class, function (TemplatedMail $mail) use ($application, $submission) {
            return $mail->key === 'tes_disc_tersedia'
                && collect($mail->to)->contains('address', $application->candidate->email)
                && str_contains($mail->body, 'https://disc.example.test/asesmen/abc')
                && str_contains($mail->body, route('tes-disc.show', $submission->token))
                && str_contains($mail->body, 'Kerjakan tes pada pagi hari.');
        });
    }

    public function test_admin_hr_can_send_mbti_link(): void
    {
        Mail::fake();

        $vacancy = $this->createVacancy(['lamaran', 'tes_mbti', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_mbti');
        $submission = MbtiSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
        ]);

        $this->actingAs($this->hrAdmin())->post(
            route('lowongan.lamaran.tes-mbti.kirim', [$vacancy, $application]),
            [
                'link_tes' => 'https://mbti.example.test/asesmen/xyz',
                'catatan' => '',
            ],
        )->assertSessionHasNoErrors();

        $submission->refresh();
        $this->assertSame('https://mbti.example.test/asesmen/xyz', $submission->link_tes);
        $this->assertNotNull($submission->sent_at);

        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail) => $mail->key === 'tes_mbti_tersedia'
            && str_contains($mail->body, route('tes-mbti.show', $submission->token)));
    }

    public function test_sending_link_requires_test_send_link_permission(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
        ]);

        $manager = User::factory()->withRole(Role::HrManager)->create();

        $this->actingAs($manager)->post(
            route('lowongan.lamaran.tes-disc.kirim', [$vacancy, $application]),
            ['link_tes' => 'https://disc.example.test/asesmen/abc'],
        )->assertForbidden();
    }

    public function test_link_must_be_a_valid_http_url(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
        ]);

        $this->actingAs($this->hrAdmin())->post(
            route('lowongan.lamaran.tes-disc.kirim', [$vacancy, $application]),
            ['link_tes' => 'javascript:alert(1)'],
        )->assertSessionHasErrors('link_tes');

        $this->assertNull($submission->fresh()->link_tes);
    }

    // ===== Candidate page =====

    public function test_candidate_page_shows_missing_link_message_before_link_is_sent(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::create([
            'application_id' => $application->id,
            'token' => Str::uuid()->toString(),
        ]);

        $this->get(route('tes-disc.show', $submission->token))
            ->assertOk()
            ->assertSee('Link tes belum tersedia');
    }

    public function test_candidate_page_shows_external_link_notes_and_upload_form(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::factory()->sent()->create([
            'application_id' => $application->id,
            'link_tes' => 'https://disc.example.test/asesmen/abc',
            'catatan' => 'Catatan untuk kandidat',
        ]);

        $this->get(route('tes-disc.show', $submission->token))
            ->assertOk()
            ->assertSee('https://disc.example.test/asesmen/abc')
            ->assertSee('Catatan untuk kandidat')
            ->assertSee('Unggah Bukti Tes')
            ->assertSee('Kirim Bukti & Lanjut');
    }

    // ===== Upload proof =====

    public function test_candidate_can_upload_multiple_proofs_and_pipeline_advances(): void
    {
        Storage::fake('public');

        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::factory()->sent()->create([
            'application_id' => $application->id,
        ]);

        $response = $this->post(route('tes-disc.submit', $submission->token), [
            'proofs' => [
                UploadedFile::fake()->image('bukti-1.jpg', 800, 600)->size(200),
                UploadedFile::fake()->image('bukti-2.png', 800, 600)->size(300),
            ],
        ]);

        $response->assertRedirect(route('tes-disc.show', $submission->token));

        $submission->refresh();
        $this->assertCount(2, $submission->proof_paths);
        $this->assertNotNull($submission->proof_uploaded_at);
        Storage::disk('public')->assertExists($submission->proof_paths[0]);
        Storage::disk('public')->assertExists($submission->proof_paths[1]);

        $discStage = $application->stages()->where('key', 'tes_disc')->first();
        $this->assertEquals(ApplicationStageStatus::Selesai, $discStage->status);

        $onboardingStage = $application->stages()->where('key', 'onboarding')->first();
        $this->assertEquals(ApplicationStageStatus::Aktif, $onboardingStage->status);

        $this->get(route('tes-disc.show', $submission->token))
            ->assertOk()
            ->assertSee('Bukti Tes DISC Sudah Diterima');
    }

    public function test_candidate_can_upload_mbti_proof_and_pipeline_advances(): void
    {
        Storage::fake('public');

        $vacancy = $this->createVacancy(['lamaran', 'tes_mbti', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_mbti');
        $submission = MbtiSubmission::factory()->sent()->create([
            'application_id' => $application->id,
        ]);

        $this->post(route('tes-mbti.submit', $submission->token), [
            'proofs' => [UploadedFile::fake()->image('bukti.jpg', 600, 400)],
        ])->assertRedirect(route('tes-mbti.show', $submission->token));

        $this->assertCount(1, $submission->fresh()->proof_paths);
        $this->assertEquals(
            ApplicationStageStatus::Selesai,
            $application->stages()->where('key', 'tes_mbti')->first()->status,
        );
    }

    public function test_upload_requires_between_one_and_five_images(): void
    {
        Storage::fake('public');

        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::factory()->sent()->create([
            'application_id' => $application->id,
        ]);

        $this->post(route('tes-disc.submit', $submission->token), [])
            ->assertSessionHasErrors('proofs');

        $proofs = collect(range(1, 6))
            ->map(fn (int $i) => UploadedFile::fake()->image("bukti-{$i}.jpg"))
            ->all();

        $this->post(route('tes-disc.submit', $submission->token), ['proofs' => $proofs])
            ->assertSessionHasErrors('proofs');

        $this->assertNull($submission->fresh()->proof_uploaded_at);
    }

    public function test_upload_rejects_non_image_and_oversized_files(): void
    {
        Storage::fake('public');

        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::factory()->sent()->create([
            'application_id' => $application->id,
        ]);

        $this->post(route('tes-disc.submit', $submission->token), [
            'proofs' => [UploadedFile::fake()->create('bukti.txt', 10, 'text/plain')],
        ])->assertSessionHasErrors('proofs.0');

        $this->post(route('tes-disc.submit', $submission->token), [
            'proofs' => [UploadedFile::fake()->image('besar.jpg')->size(6000)],
        ])->assertSessionHasErrors('proofs.0');

        $this->assertNull($submission->fresh()->proof_uploaded_at);
    }

    public function test_candidate_cannot_upload_after_stage_is_completed(): void
    {
        Storage::fake('public');

        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        $submission = DiscSubmission::factory()->sent()->create([
            'application_id' => $application->id,
        ]);

        $application->stages()->where('key', 'tes_disc')->update(['status' => ApplicationStageStatus::Selesai]);

        $this->post(route('tes-disc.submit', $submission->token), [
            'proofs' => [UploadedFile::fake()->image('bukti.jpg')],
        ])->assertRedirect(route('tes-disc.show', $submission->token));

        $this->assertNull($submission->fresh()->proof_uploaded_at);
    }

    // ===== Manual advance guard =====

    public function test_manual_advance_from_disc_without_proof_is_rejected(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        DiscSubmission::factory()->sent()->create(['application_id' => $application->id]);

        $this->actingAs($this->hrAdmin())
            ->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]))
            ->assertSessionHasErrors('pipeline');

        $this->assertEquals(
            ApplicationStageStatus::Aktif,
            $application->stages()->where('key', 'tes_disc')->first()->status,
        );
    }

    public function test_staff_can_reject_before_candidate_uploads_proof(): void
    {
        $vacancy = $this->createVacancy(['lamaran', 'tes_disc', 'onboarding']);
        $application = $this->makeApplicationAt($vacancy, 'tes_disc');
        DiscSubmission::factory()->sent()->create(['application_id' => $application->id]);

        $this->actingAs($this->hrAdmin())
            ->post(route('lowongan.lamaran.gagal', [$vacancy, $application]))
            ->assertSessionHasNoErrors();

        $this->assertEquals(
            ApplicationStageStatus::Gagal,
            $application->stages()->where('key', 'tes_disc')->first()->status,
        );
    }

    // ===== Cleanup checks =====

    public function test_question_routes_and_permissions_are_removed(): void
    {
        $this->assertFalse(Route::has('soal-disc.index'));
        $this->assertFalse(Route::has('soal-mbti.index'));
        $this->assertNotContains('disc-question.view', Permissions::keys());
        $this->assertNotContains('mbti-question.view', Permissions::keys());
        $this->assertContains(Permissions::TEST_SEND_LINK, Permissions::keys());
    }
}
