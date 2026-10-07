<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permissions::catalog() as $key => $meta) {
            $permission = Permission::updateOrCreate(
                ['key' => $key],
                ['label' => $meta['label'], 'group' => $meta['group']],
            );

            DB::table('permission_role')->where('permission_id', $permission->id)->delete();

            $roleIds = Role::whereIn('key', $meta['roles'])->pluck('id');

            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleId,
                    'permission_id' => $permission->id,
                ]);
            }
        }

        // Drop assignments for keys removed from the catalog.
        Permission::whereNotIn('key', Permissions::keys())->delete();

        User::flushPermissionCache();
    }
}
