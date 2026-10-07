<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_pages_require_permissions(): void
    {
        $employee = User::factory()->withRole(Role::Employee)->create();

        $this->actingAs($employee)->get(route('pengaturan.peran.index'))->assertForbidden();
        $this->actingAs($employee)->get(route('pengaturan.peran.create'))->assertForbidden();
        $this->actingAs($employee)->post(route('pengaturan.peran.store'), [])->assertForbidden();
    }

    public function test_hr_admin_can_create_role_without_permissions(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)->get(route('pengaturan.peran.index'))->assertOk();

        $this->actingAs($admin)->post(route('pengaturan.peran.store'), [
            'key' => 'supervisor_unit',
            'label' => 'Supervisor Unit',
        ])->assertRedirect(route('pengaturan.peran.index'));

        $role = Role::where('key', 'supervisor_unit')->firstOrFail();
        $this->assertSame('Supervisor Unit', $role->label);

        // New roles start without permissions.
        $this->assertFalse($role->permissions()->exists());
    }

    public function test_role_key_is_immutable(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $role = Role::where('key', Role::Employee)->firstOrFail();

        $this->actingAs($admin)->put(route('pengaturan.peran.update', $role), [
            'key' => 'changed',
            'label' => 'Karyawan Baru',
        ])->assertRedirect(route('pengaturan.peran.index'));

        $this->assertSame(Role::Employee, $role->fresh()->key);
        $this->assertSame('Karyawan Baru', $role->fresh()->label);
    }

    public function test_role_with_users_cannot_be_deleted(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $role = Role::where('key', Role::Employee)->firstOrFail();
        User::factory()->withRole(Role::Employee)->create();

        $this->actingAs($admin)
            ->delete(route('pengaturan.peran.destroy', $role))
            ->assertStatus(422);

        $this->assertTrue(Role::where('key', Role::Employee)->exists());
    }

    public function test_last_access_holder_role_cannot_be_deleted(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $admin->update(['role_id' => Role::where('key', Role::HrManager)->value('id')]);

        // Grant only role.delete to hr_manager so the request reaches the guard.
        Role::where('key', Role::HrManager)->firstOrFail()->permissions()->attach(
            Permission::where('key', Permissions::ROLE_DELETE)->value('id')
        );
        User::flushPermissionCache();

        $hrAdmin = Role::where('key', Role::HrAdmin)->firstOrFail();
        $this->assertFalse($hrAdmin->users()->exists());

        $this->actingAs($admin)
            ->delete(route('pengaturan.peran.destroy', $hrAdmin))
            ->assertSessionHasErrors('role');

        $this->assertTrue(Role::where('key', Role::HrAdmin)->exists());
    }

    public function test_unused_role_can_be_deleted(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $role = Role::create(['key' => 'arsip', 'label' => 'Arsip']);

        $this->actingAs($admin)
            ->delete(route('pengaturan.peran.destroy', $role))
            ->assertRedirect(route('pengaturan.peran.index'));

        $this->assertFalse(Role::where('key', 'arsip')->exists());
    }

    public function test_new_role_gets_access_through_matrix(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $role = Role::create(['key' => 'supervisor_unit', 'label' => 'Supervisor Unit']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)->get(route('unit.index'))->assertForbidden();

        $role->permissions()->sync(
            Permission::whereIn('key', [Permissions::MENU_UNITS, Permissions::UNIT_VIEW])->pluck('id')
        );
        User::flushPermissionCache();

        $this->actingAs($user)->get(route('unit.index'))->assertOk();
    }
}
