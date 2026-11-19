<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add the audit-log menu / view permissions for existing installations.
     *
     * PermissionSeeder rebuilds the whole catalog (and resets role overrides),
     * so a deployment that only runs migrations needs this targeted insert.
     */
    public function up(): void
    {
        $now = now();

        $permissions = [
            'menu.audit-log' => ['label' => 'Sidemenu: Log Audit', 'group' => 'Menu'],
            'audit-log.view' => ['label' => 'Lihat log audit', 'group' => 'Log Audit'],
        ];

        foreach ($permissions as $key => $meta) {
            $exists = DB::table('permissions')->where('key', $key)->exists();

            if ($exists) {
                DB::table('permissions')->where('key', $key)->update([
                    'label' => $meta['label'],
                    'group' => $meta['group'],
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('permissions')->insert([
                'key' => $key,
                'label' => $meta['label'],
                'group' => $meta['group'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $hrAdminId = DB::table('roles')->where('key', 'hr_admin')->value('id');

        if ($hrAdminId === null) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('key', array_keys($permissions))
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->insertOrIgnore([
                'role_id' => $hrAdminId,
                'permission_id' => $permissionId,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissionIds = DB::table('permissions')
            ->whereIn('key', ['menu.audit-log', 'audit-log.view'])
            ->pluck('id');

        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('key', ['menu.audit-log', 'audit-log.view'])->delete();
    }
};
