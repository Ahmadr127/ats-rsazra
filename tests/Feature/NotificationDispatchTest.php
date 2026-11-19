<?php

namespace Tests\Feature;

use App\Enums\ApplicationStageStatus;
use App\Enums\QuestionType;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\Employee;
use App\Models\JobTemplate;
use App\Models\Question;
use App\Models\QuestionBankTemplate;
use App\Models\QuestionOption;
use App\Models\Role;
use App\Models\Stage;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use App\Services\EmailNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * End-to-end assertions that each customer-facing email template is dispatched
 * with the correct key and recipient. Complements the template-rendering unit
 * tests in EmailNotificationServiceTest.
 */
class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
        $this->artisan('db:seed', ['--class' => 'EmailTemplateSeeder']);
    }

    /**
     * @param  list<string>  $stageKeys
     */
    private function createVacancy(array $stageKeys, ?Unit $unit = null): Vacancy
    {
        $unit ??= Unit::factory()->create();

        $template = WorkflowTemplate::factory()->create();

        collect($stageKeys)->each(function (string $key, int $index) use ($template) {
            $stage = Stage::where('key', $key)->firstOrFail();
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        $template->load('stages');
        $snapshot = WorkflowTemplateSnapshot::createFromTemplate($template);

        return Vacancy::factory()->published()->create([
            'unit_id' => $unit->id,
            'workflow_template_snapshot_id' => $snapshot->id,
        ]);
    }

    private function createVacancyWithCompetencyTest(Unit $unit): Vacancy
    {
        $vacancy = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'tes_kompetensi', 'onboarding'], $unit);

        $questionBank = QuestionBankTemplate::factory()->create();
        $question = Question::factory()->create([
            'question_bank_template_id' => $questionBank->id,
            'tipe' => QuestionType::Mc->value,
            'nilai_poin' => 10,
            'urutan' => 1,
        ]);
        QuestionOption::factory()->correct()->create(['question_id' => $question->id]);
        QuestionOption::factory()->create(['question_id' => $question->id]);

        $vacancyTest = VacancyTest::create(['vacancy_id' => $vacancy->id, 'batas_waktu_menit' => 60]);
        $vacancyTest->questions()->attach($question->id, ['urutan' => 1]);
        VacancyTestSnapshot::createFromVacancyTest($vacancyTest);

        return $vacancy;
    }

    /** @param array<int, ApplicationStageStatus> $stageStatuses */
    private function makeApplication(Vacancy $vacancy, array $stageStatuses = []): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => Candidate::factory()->create()->id,
        ]);

        $snapshotStages = $vacancy->workflowTemplateSnapshot->stages->sortBy('position')->values();

        foreach ($snapshotStages as $index => $stage) {
            $status = $stageStatuses[$index] ?? ($index === 0
                ? ApplicationStageStatus::Selesai
                : ($index === 1 ? ApplicationStageStatus::Aktif : ApplicationStageStatus::Pending));

            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    private function makeUnitHead(Unit $unit): User
    {
        $user = User::factory()->withRole(Role::UnitHead)->create();

        Employee::factory()->create([
            'user_id' => $user->id,
            'unit_id' => $unit->id,
        ]);

        return $user;
    }

    private function makeHrAdmin(): User
    {
        return User::factory()->hrAdmin()->create();
    }

    private function assertMailQueuedTo(string $key, string $address): void
    {
        Mail::assertQueued(TemplatedMail::class, fn (TemplatedMail $mail) => $mail->key === $key
            && collect($mail->to)->contains('address', $address));
    }

    private function failedApplication(Vacancy $vacancy, Candidate $candidate, string $gagalKey): Application
    {
        $vacancy->load('workflowTemplateSnapshot.stages');

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
        ]);

        $passedGagal = false;

        foreach ($vacancy->workflowTemplateSnapshot->stages as $stage) {
            if ($stage->key === $gagalKey) {
                $status = ApplicationStageStatus::Gagal;
                $passedGagal = true;
            } elseif ($passedGagal) {
                $status = ApplicationStageStatus::Pending;
            } else {
                $status = ApplicationStageStatus::Selesai;
            }

            ApplicationStage::factory()->create([
                'application_id' => $application->id,
                'position' => $stage->position,
                'key' => $stage->key,
                'nama' => $stage->nama,
                'status' => $status,
            ]);
        }

        return $application;
    }

    public function test_advance_into_tes_kompetensi_sends_tes_tersedia(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancyWithCompetencyTest(Unit::factory()->create());
        $application = $this->makeApplication($vacancy);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        $this->assertMailQueuedTo('tes_tersedia', $candidateEmail);
    }

    public function test_advance_into_tes_disc_does_not_send_email_automatically(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'tes_disc', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $this->actingAs($admin)->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        Mail::assertNothingQueued();
        $this->assertDatabaseHas('disc_submissions', ['application_id' => $application->id]);
    }

    public function test_advance_into_tes_mbti_does_not_send_email_automatically(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'tes_mbti', 'onboarding']);
        $application = $this->makeApplication($vacancy);

        $this->actingAs($admin)->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        Mail::assertNothingQueued();
        $this->assertDatabaseHas('mbti_submissions', ['application_id' => $application->id]);
    }

    public function test_advance_from_skrining_to_non_skrining_sends_lolos_skrining_cv(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        // Next stage after skrining must be neither a test nor a silent stage for
        // the lolos_skrining_cv branch to fire (surat_penawaran qualifies).
        $vacancy = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'surat_penawaran', 'onboarding']);
        $application = $this->makeApplication($vacancy);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        $this->assertMailQueuedTo('lolos_skrining_cv', $candidateEmail);
        Mail::assertNotQueued(TemplatedMail::class, fn (TemplatedMail $mail) => $mail->key === 'transisi_tahap');
    }

    public function test_advance_from_skrining_to_skrining_sends_transisi_tahap_not_lolos_skrining_cv(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'skrining_cv_user', 'onboarding']);
        $application = $this->makeApplication($vacancy);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.lamaran.lanjut', [$vacancy, $application]));

        $this->assertMailQueuedTo('transisi_tahap', $candidateEmail);
        Mail::assertNotQueued(TemplatedMail::class, fn (TemplatedMail $mail) => $mail->key === 'lolos_skrining_cv');
    }

    public function test_wawancara_schedule_sends_wawancara_dijadwalkan_to_candidate(): void
    {
        Mail::fake();

        $unit = Unit::factory()->create();
        $admin = $this->makeHrAdmin();
        $interviewer = $this->makeUnitHead($unit);
        $vacancy = $this->createVacancy(['lamaran', 'wawancara_user', 'onboarding'], $unit);
        $application = $this->makeApplication($vacancy);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.wawancara.jadwal', [$vacancy, $application]), [
            'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'lokasi' => 'Ruang Meeting',
            'interviewer_id' => $interviewer->id,
        ]);

        $this->assertMailQueuedTo('wawancara_dijadwalkan', $candidateEmail);
    }

    public function test_send_offering_letter_sends_surat_penawaran_email_to_candidate(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplication($vacancy);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.surat-penawaran.kirim', [$vacancy, $application]), [
            'jabatan_ditawarkan' => 'Perawat Rawat Inap',
            'gaji' => 'Rp 5.000.000',
            'tanggal_mulai' => now()->addDays(14)->toDateString(),
            'surat' => UploadedFile::fake()->create('surat-penawaran.pdf', 100, 'application/pdf'),
        ]);

        $this->assertMailQueuedTo('surat_penawaran', $candidateEmail);
    }

    public function test_mcu_schedule_sends_instruksi_mcu_email_to_candidate(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Aktif,
            3 => ApplicationStageStatus::Pending,
        ]);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.mcu.jadwal', [$vacancy, $application]), [
            'jadwal' => now()->addDays(3)->format('Y-m-d\TH:i'),
            'lokasi' => 'RS Azra Lt. 2',
        ]);

        $this->assertMailQueuedTo('instruksi_mcu', $candidateEmail);
    }

    public function test_onboarding_invitation_sends_undangan_onboarding_email_to_candidate(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $vacancy = $this->createVacancy(['lamaran', 'surat_penawaran', 'mcu', 'onboarding']);
        $application = $this->makeApplication($vacancy, [
            0 => ApplicationStageStatus::Selesai,
            1 => ApplicationStageStatus::Selesai,
            2 => ApplicationStageStatus::Selesai,
            3 => ApplicationStageStatus::Aktif,
        ]);
        $candidateEmail = $application->candidate->email;

        $this->actingAs($admin)->post(route('lowongan.onboarding.undangan', [$vacancy, $application]), [
            'tanggal_bergabung' => now()->addDays(30)->toDateString(),
            'catatan' => 'Hadir pukul 08.00 WIB.',
        ]);

        $this->assertMailQueuedTo('undangan_onboarding', $candidateEmail);
    }

    public function test_callback_invite_sends_undangan_panggil_kembali_with_candidate_recipient(): void
    {
        Mail::fake();

        $admin = $this->makeHrAdmin();
        $template = JobTemplate::factory()->create();
        $prior = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding']);
        $target = $this->createVacancy(['lamaran', 'skrining_cv_hr', 'wawancara_user', 'onboarding']);

        $candidate = Candidate::factory()->create();
        $this->failedApplication($prior, $candidate, 'wawancara_user');

        $this->actingAs($admin)->post(route('callback.invite', $target), [
            'candidate_ids' => [$candidate->id],
        ]);

        $this->assertMailQueuedTo('undangan_panggil_kembali', $candidate->email);
    }

    public function test_dispatch_with_missing_template_is_silent_and_sends_nothing(): void
    {
        Mail::fake();

        $service = app(EmailNotificationService::class);

        $service->dispatch('template_yang_tidak_ada', 'kandidat@example.com', ['nama_kandidat' => 'Budi']);

        Mail::assertNothingQueued();
    }
}
