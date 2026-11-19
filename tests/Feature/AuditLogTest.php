<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\EmailTemplate;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\EmailNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Queue\Events\WorkerStarting;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('audit.enabled', true);
    }

    public function test_only_permission_holder_can_view_audit_log(): void
    {
        $employee = User::factory()->withRole(Role::Employee)->create();

        $this->actingAs($employee)
            ->get(route('audit-log.index'))
            ->assertForbidden();

        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)
            ->get(route('audit-log.index'))
            ->assertOk()
            ->assertSee('Log Audit');
    }

    public function test_audit_permissions_appear_in_access_matrix(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)
            ->get(route('pengaturan.hak-akses.index'))
            ->assertOk()
            ->assertSee('Log Audit')
            ->assertSee('audit-log.view');
    }

    public function test_audit_log_detail_can_be_viewed(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $log = AuditLog::create([
            'category' => 'test',
            'event' => 'test.detail',
            'status' => 'success',
            'severity' => 'info',
            'subject' => 'Detail audit',
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('audit-log.show', $log))
            ->assertOk()
            ->assertSee('test.detail');
    }

    public function test_model_create_update_delete_are_captured(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->actingAs($admin);

        $unit = Unit::create(['nama' => 'ICU']);
        $id = $unit->id;

        $unit->update(['nama' => 'IGD']);
        $unit->delete();

        $created = AuditLog::where('event', 'eloquent.created: '.Unit::class)
            ->where('auditable_id', $id)
            ->first();

        $this->assertNotNull($created);
        $this->assertSame('ICU', $created->new_values['nama']);
        $this->assertSame($admin->id, $created->actor_id);
        $this->assertSame($admin->name, $created->actor_name);

        $updated = AuditLog::where('event', 'eloquent.updated: '.Unit::class)
            ->where('auditable_id', $id)
            ->first();

        $this->assertNotNull($updated);
        $this->assertSame('ICU', $updated->old_values['nama']);
        $this->assertSame('IGD', $updated->new_values['nama']);

        $deleted = AuditLog::where('event', 'eloquent.deleted: '.Unit::class)
            ->where('auditable_id', $id)
            ->first();

        $this->assertNotNull($deleted);
        $this->assertSame('IGD', $deleted->old_values['nama']);
        $this->assertNull($deleted->new_values);
    }

    public function test_sensitive_fields_are_redacted(): void
    {
        User::factory()->create(['username' => 'redaksi']);

        $log = AuditLog::where('event', 'eloquent.created: '.User::class)
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('redaksi', $log->new_values['username']);
        $this->assertSame('[redacted]', $log->new_values['password']);
    }

    public function test_login_is_captured(): void
    {
        $user = User::factory()->create(['username' => 'audit_login']);

        $this->post('/login', [
            'username' => 'audit_login',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('audit_logs', [
            'category' => 'auth',
            'event' => 'auth.login',
            'status' => 'success',
            'auditable_id' => $user->id,
        ]);
    }

    public function test_queued_email_and_job_are_captured(): void
    {
        EmailTemplate::unguarded(fn () => EmailTemplate::firstOrCreate(
            ['key' => 'audit_test'],
            ['key' => 'audit_test', 'deskripsi' => 'Audit test', 'subjek' => 'Halo Audit', 'isi' => 'Body audit'],
        ));

        app(EmailNotificationService::class)->dispatch('audit_test', 'kandidat@example.com');

        $this->assertDatabaseHas('audit_logs', [
            'category' => 'mail',
            'event' => 'email.sending',
            'status' => 'sending',
        ]);

        $sent = AuditLog::where('event', 'email.sent')->first();

        $this->assertNotNull($sent);
        $this->assertSame('Halo Audit', $sent->subject);
        $this->assertSame('audit_test', $sent->context['template_key']);
        $this->assertSame('kandidat@example.com', $sent->context['to'][0]['address']);

        $this->assertDatabaseHas('audit_logs', [
            'category' => 'queue',
            'event' => 'job.processing',
            'status' => 'processing',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'category' => 'queue',
            'event' => 'job.processed',
            'status' => 'processed',
        ]);
    }

    public function test_worker_events_are_captured(): void
    {
        Event::dispatch(new WorkerStarting('database', 'default', new WorkerOptions('default')));

        $this->assertDatabaseHas('audit_logs', [
            'category' => 'worker',
            'event' => 'worker.started',
            'status' => 'started',
        ]);
    }

    public function test_prune_removes_logs_older_than_retention(): void
    {
        AuditLog::create([
            'category' => 'test',
            'event' => 'test.old',
            'severity' => 'info',
            'created_at' => now()->subDays(100),
        ]);

        AuditLog::create([
            'category' => 'test',
            'event' => 'test.new',
            'severity' => 'info',
            'created_at' => now(),
        ]);

        $this->artisan('audit:prune', ['--days' => 90])->assertSuccessful();

        $this->assertSame(1, AuditLog::count());
        $this->assertDatabaseHas('audit_logs', ['event' => 'test.new']);
        $this->assertDatabaseMissing('audit_logs', ['event' => 'test.old']);
    }

    public function test_audit_can_be_disabled_at_runtime(): void
    {
        config()->set('audit.enabled', false);

        Unit::create(['nama' => 'Tanpa Audit']);

        $this->assertSame(0, AuditLog::count());
    }
}
