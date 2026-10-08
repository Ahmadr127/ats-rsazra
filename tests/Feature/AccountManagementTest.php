<?php

namespace Tests\Feature;

use App\Http\Controllers\AccountController;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_account_list(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();
        $account = User::factory()->create();
        $employee->update(['user_id' => $account->id]);

        $response = $this->actingAs($admin)->get(route('akun.index'));

        $response->assertStatus(200);
        $response->assertViewIs('accounts.index');
    }

    public function test_non_hr_admin_cannot_view_account_list(): void
    {
        foreach ([Role::HrManager, Role::UnitHead, Role::Director, Role::Employee] as $role) {
            $user = User::factory()->withRole($role)->create([]);
            $response = $this->actingAs($user)->get(route('akun.index'));
            $response->assertStatus(403);
        }
    }

    public function test_guest_cannot_view_account_list(): void
    {
        $response = $this->get(route('akun.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_account_list_is_searchable_by_username(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        User::factory()->create(['username' => 'budisantoso']);
        User::factory()->create(['username' => 'sariindah']);

        $response = $this->actingAs($admin)->get(route('akun.index', ['q' => 'budi']));

        $response->assertStatus(200);
        $response->assertSee('budisantoso');
        $response->assertDontSee('sariindah');
    }

    public function test_account_list_is_searchable_by_employee_name(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $emp1 = Employee::factory()->create(['nama_karyawan' => 'Ahmad Fauzi']);
        $user1 = User::factory()->create(['username' => 'ahmadfauzi']);
        $emp1->update(['user_id' => $user1->id]);

        $emp2 = Employee::factory()->create(['nama_karyawan' => 'Sari Dewi']);
        $user2 = User::factory()->create(['username' => 'saridewi']);
        $emp2->update(['user_id' => $user2->id]);

        $response = $this->actingAs($admin)->get(route('akun.index', ['q' => 'Ahmad']));

        $response->assertStatus(200);
        $response->assertSee('ahmadfauzi');
        $response->assertDontSee('saridewi');
    }

    public function test_account_list_is_filterable_by_role(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        User::factory()->withRole(Role::Employee)->create(['username' => 'empuser']);
        User::factory()->withRole(Role::Director)->create(['username' => 'directoruser']);

        $response = $this->actingAs($admin)->get(route('akun.index', ['role_id' => Role::where('key', Role::Employee)->value('id')]));

        $response->assertStatus(200);
        $response->assertSee('empuser');
        $response->assertDontSee('directoruser');
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_view_create_account_form(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        Employee::factory()->count(2)->create();

        $response = $this->actingAs($admin)->get(route('akun.create'));

        $response->assertStatus(200);
        $response->assertViewIs('accounts.create');
    }

    public function test_search_available_employees_excludes_employees_with_accounts(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employeeWithAccount = Employee::factory()->create();
        $employeeWithAccount->update(['user_id' => User::factory()->create()->id]);
        $employeeWithoutAccount = Employee::factory()->create(['nama_karyawan' => 'Karyawan Tanpa Akun']);

        $response = $this->actingAs($admin)->get(route('akun.karyawan-cari'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['results', 'has_more']);
        $ids = collect($response->json('results'))->pluck('id');
        $this->assertTrue($ids->contains($employeeWithoutAccount->id));
        $this->assertFalse($ids->contains($employeeWithAccount->id));
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_create_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create(['nama_karyawan' => 'Budi Santoso']);

        $response = $this->actingAs($admin)->post(route('akun.store'), [
            'employee_id' => $employee->id,
            'username' => 'budisantoso',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
        ]);

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', [
            'username' => 'budisantoso',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
            'must_change_password' => true,
            'is_active' => true,
        ]);
        $employee->refresh();
        $this->assertNotNull($employee->user_id);
    }

    public function test_username_must_be_unique(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        User::factory()->create(['username' => 'existinguser']);
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->post(route('akun.store'), [
            'employee_id' => $employee->id,
            'username' => 'existinguser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
        ]);

        $response->assertSessionHasErrors('username');
    }

    public function test_employee_cannot_have_more_than_one_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();
        $existingUser = User::factory()->create();
        $employee->update(['user_id' => $existingUser->id]);

        $response = $this->actingAs($admin)->post(route('akun.store'), [
            'employee_id' => $employee->id,
            'username' => 'newusername',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
        ]);

        $response->assertSessionHasErrors('employee_id');
    }

    public function test_role_assignment_is_validated(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->post(route('akun.store'), [
            'employee_id' => $employee->id,
            'username' => 'validuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 999999,
        ]);

        $response->assertSessionHasErrors('role_id');
    }

    public function test_username_may_contain_dots_like_existing_accounts(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $employee = Employee::factory()->create();

        $response = $this->actingAs($admin)->post(route('akun.store'), [
            'employee_id' => $employee->id,
            'username' => 'budi.santoso',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
        ]);

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', ['username' => 'budi.santoso']);
    }

    public function test_account_with_dotted_username_can_be_updated(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->withRole(Role::Employee)->create(['username' => 'umar.baidhowi']);

        $response = $this->actingAs($admin)->patch(route('akun.update', $account), [
            'username' => 'umar.baidhowi',
            'role_id' => $account->role_id,
        ]);

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', ['id' => $account->id, 'username' => 'umar.baidhowi']);
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function test_hr_admin_can_edit_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->withRole(Role::Employee)->create(['username' => 'testedit']);

        $response = $this->actingAs($admin)->get(route('akun.edit', $account));

        $response->assertStatus(200);
        $response->assertViewIs('accounts.edit');
    }

    public function test_hr_admin_can_change_role(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->withRole(Role::Employee)->create(['username' => 'testchange']);

        $response = $this->actingAs($admin)->patch(route('akun.update', $account), [
            'username' => 'testchange',
            'role_id' => Role::where('key', Role::UnitHead)->value('id'),
        ]);

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', [
            'id' => $account->id,
            'role_id' => Role::where('key', Role::UnitHead)->value('id'),
        ]);
    }

    public function test_password_reset_sets_must_change_password_flag(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->create(['must_change_password' => false, 'username' => 'testreset']);

        $this->actingAs($admin)->patch(route('akun.update', $account), [
            'username' => 'testreset',
            'role_id' => $account->role_id,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $account->refresh();
        $this->assertTrue($account->must_change_password);
    }

    public function test_update_without_password_does_not_change_password(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->create(['must_change_password' => false, 'username' => 'testnopwd']);
        $originalPassword = $account->password;

        $this->actingAs($admin)->patch(route('akun.update', $account), [
            'username' => 'testnopwd',
            'role_id' => $account->role_id,
        ]);

        $account->refresh();
        $this->assertEquals($originalPassword, $account->password);
        $this->assertFalse($account->must_change_password);
    }

    // ── Toggle Aktif ───────────────────────────────────────────────────────────

    public function test_hr_admin_can_deactivate_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patch(route('akun.toggle-aktif', $account));

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', ['id' => $account->id, 'is_active' => false]);
    }

    public function test_hr_admin_can_activate_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $account = User::factory()->inactive()->create();

        $response = $this->actingAs($admin)->patch(route('akun.toggle-aktif', $account));

        $response->assertRedirect(route('akun.index'));
        $this->assertDatabaseHas('users', ['id' => $account->id, 'is_active' => true]);
    }

    public function test_hr_admin_cannot_deactivate_own_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->patch(route('akun.toggle-aktif', $admin));

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_active' => true]);
    }

    public function test_hr_admin_cannot_edit_own_account(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $response = $this->actingAs($admin)->patch(route('akun.update', $admin), [
            'username' => 'newusername',
            'role_id' => Role::where('key', Role::Employee)->value('id'),
        ]);

        $response->assertStatus(403);
    }

    public function test_deactivated_account_cannot_login(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif']);

        $response = $this->from(route('login'))->post(route('login'), [
            'username' => 'nonaktif',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_deactivated_account_sees_specific_error_message(): void
    {
        User::factory()->inactive()->create(['username' => 'nonaktif']);

        $response = $this->from(route('login'))->post(route('login'), [
            'username' => 'nonaktif',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['username' => 'Akun Anda telah dinonaktifkan. Hubungi HR Admin.']);
    }

    // ── Username Generation ────────────────────────────────────────────────────

    public function test_username_generation_from_two_word_name(): void
    {
        $this->assertEquals('budisantoso', AccountController::generateUsername('Budi Santoso'));
    }

    public function test_username_generation_from_single_word_name(): void
    {
        $this->assertEquals('budi', AccountController::generateUsername('Budi'));
    }

    public function test_username_generation_from_three_word_name(): void
    {
        $this->assertEquals('budisaputra', AccountController::generateUsername('Budi Maulana Saputra'));
    }

    public function test_username_generation_strips_diacritics(): void
    {
        $this->assertEquals('andresanchez', AccountController::generateUsername('André Sánchez'));
    }

    public function test_username_generation_strips_special_characters(): void
    {
        $this->assertEquals('budidr', AccountController::generateUsername('Budi dr.'));
    }
}
