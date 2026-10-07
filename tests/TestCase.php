<?php

namespace Tests;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Seed RBAC defaults only when the database was migrated
        // (RefreshDatabase). Pure HTTP tests without DB skip this.
        if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
            $this->artisan('db:seed', ['--class' => RoleSeeder::class]);
            $this->artisan('db:seed', ['--class' => PermissionSeeder::class]);
        }
    }
}
