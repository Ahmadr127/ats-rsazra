<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'permission:'.Permissions::UNIT_VIEW])->get('/test-perm-only', fn () => 'ok');
        Route::middleware(['web', 'auth', 'permission:'.Permissions::UNIT_VIEW.','.Permissions::UNIT_CREATE])->get('/test-perm-any', fn () => 'ok');
    }

    public function test_user_with_permission_can_access(): void
    {
        $user = User::factory()->hrAdmin()->create();

        $this->actingAs($user)->get('/test-perm-only')->assertOk();
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/test-perm-only')->assertForbidden();
    }

    public function test_any_of_multiple_permissions_grants_access(): void
    {
        $user = User::factory()->hrAdmin()->create();

        $this->actingAs($user)->get('/test-perm-any')->assertOk();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
