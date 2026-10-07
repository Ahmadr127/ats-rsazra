<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\InterviewStageMap;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private function roleId(string $key): int
    {
        return Role::where('key', $key)->value('id');
    }

    private function permissionId(string $key): int
    {
        return Permission::where('key', $key)->value('id');
    }

    public function test_permission_catalog_is_seeded(): void
    {
        $this->assertSame(count(Permissions::keys()), Permission::count());
        $this->assertSame(count(Permissions::keys()), DB::table('permission_role')->distinct('permission_id')->count('permission_id'));
    }

    public function test_hr_admin_has_every_permission(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        // Documented exceptions (pre-RBAC behavior preserved): hr_admin
        // schedules interviews but never judges them.
        $except = [
            Permissions::INTERVIEW_DECIDE_USER,
            Permissions::INTERVIEW_DECIDE_MANAGER,
            Permissions::INTERVIEW_DECIDE_DIRECTOR,
        ];

        foreach (Permissions::keys() as $key) {
            if (in_array($key, $except, true)) {
                $this->assertFalse($admin->hasPermission($key), "hr_admin should NOT have {$key}");

                continue;
            }

            $this->assertTrue($admin->hasPermission($key), "hr_admin missing {$key}");
        }
    }

    public function test_unit_head_defaults(): void
    {
        $head = User::factory()->withRole(Role::UnitHead)->create();

        $this->assertTrue($head->hasPermission(Permissions::SCREENING_DECIDE));
        $this->assertTrue($head->hasPermission(Permissions::INTERVIEW_DECIDE_USER));
        $this->assertTrue($head->hasPermission(Permissions::APPLICATION_ADVANCE));
        $this->assertFalse($head->hasPermission(Permissions::VACANCY_VIEW_ORG));
        $this->assertFalse($head->hasPermission(Permissions::RBAC_MANAGE));
        $this->assertFalse($head->hasPermission(Permissions::INTERVIEW_DECIDE_MANAGER));
    }

    public function test_interview_stage_map(): void
    {
        $this->assertSame(
            InterviewStageMap::MANAGER,
            InterviewStageMap::stageKeyForDecider(User::factory()->withRole(Role::HrManager)->create())
        );
        $this->assertSame(
            InterviewStageMap::DIRECTOR,
            InterviewStageMap::stageKeyForDecider(User::factory()->withRole(Role::Director)->create())
        );
        $this->assertSame(
            InterviewStageMap::USER,
            InterviewStageMap::stageKeyForDecider(User::factory()->withRole(Role::UnitHead)->create())
        );
        $this->assertNull(
            InterviewStageMap::stageKeyForDecider(User::factory()->hrAdmin()->create())
        );
    }

    public function test_rbac_page_requires_manage_permission(): void
    {
        $employee = User::factory()->withRole(Role::Employee)->create();

        $this->actingAs($employee)->get(route('pengaturan.hak-akses.index'))->assertForbidden();
        $this->actingAs($employee)->put(route('pengaturan.hak-akses.update'), [])->assertForbidden();
    }

    public function test_rbac_page_visible_to_hr_admin(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->get(route('pengaturan.hak-akses.index'));

        $response->assertOk();
        $response->assertSee('menu.rbac', false);
    }

    /**
     * @return array<int, list<int>>
     */
    private function currentMatrix(): array
    {
        $matrix = [];

        foreach (Role::all() as $role) {
            $matrix[$role->id] = [];
        }

        foreach (DB::table('permission_role')->get() as $row) {
            $matrix[$row->role_id][] = $row->permission_id;
        }

        return $matrix;
    }

    public function test_matrix_update_revolkes_and_enforcement_follows(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $matrix = $this->currentMatrix();
        $hrAdminId = $this->roleId(Role::HrAdmin);
        $unitViewId = $this->permissionId(Permissions::UNIT_VIEW);
        $matrix[$hrAdminId] = array_values(array_diff($matrix[$hrAdminId], [$unitViewId]));

        $this->actingAs($admin)
            ->put(route('pengaturan.hak-akses.update'), ['permissions' => $matrix])
            ->assertRedirect(route('pengaturan.hak-akses.index'));

        $this->assertFalse($admin->fresh()->hasPermission(Permissions::UNIT_VIEW));
        $this->actingAs($admin)->get(route('unit.index'))->assertForbidden();
    }

    public function test_matrix_update_can_grant_new_permission(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $head = User::factory()->withRole(Role::UnitHead)->create();

        $this->actingAs($head)->get(route('unit.index'))->assertForbidden();

        $matrix = $this->currentMatrix();
        $matrix[$this->roleId(Role::UnitHead)][] = $this->permissionId(Permissions::UNIT_VIEW);

        $this->actingAs($admin)
            ->put(route('pengaturan.hak-akses.update'), ['permissions' => $matrix])
            ->assertRedirect(route('pengaturan.hak-akses.index'));

        $this->assertTrue($head->fresh()->hasPermission(Permissions::UNIT_VIEW));
        $this->actingAs($head)->get(route('unit.index'))->assertOk();
    }

    public function test_matrix_update_cannot_remove_last_access_holder(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $matrix = $this->currentMatrix();
        $hrAdminId = $this->roleId(Role::HrAdmin);
        $rbacId = $this->permissionId(Permissions::RBAC_MANAGE);
        $matrix[$hrAdminId] = array_values(array_diff($matrix[$hrAdminId], [$rbacId]));

        $this->actingAs($admin)
            ->put(route('pengaturan.hak-akses.update'), ['permissions' => $matrix])
            ->assertSessionHasErrors('permissions');

        $this->assertTrue($admin->fresh()->hasPermission(Permissions::RBAC_MANAGE));
    }
}
