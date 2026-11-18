<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Split the single `screening.decide` permission into two so the
     * access-control UI maps one permission to one pipeline form:
     * `screening.decide-hr` (Skrining CV HR) and `screening.decide-user`
     * (Skrining CV User). Existing role assignments are copied to both new
     * permissions so current effective access is preserved.
     */
    public function up(): void
    {
        $old = DB::table('permissions')->where('key', 'screening.decide')->first();

        if (! $old) {
            // Nothing to migrate (fresh install handled by PermissionSeeder).
            return;
        }

        $roleIds = DB::table('permission_role')->where('permission_id', $old->id)->pluck('role_id')->all();

        DB::table('permissions')->where('id', $old->id)->update([
            'key' => 'screening.decide-hr',
            'label' => 'Form Pipeline: Skrining CV HR — Putuskan hasil',
            'updated_at' => now(),
        ]);

        $now = now();
        $newUserId = DB::table('permissions')->insertGetId([
            'key' => 'screening.decide-user',
            'label' => 'Form Pipeline: Skrining CV User — Putuskan hasil',
            'group' => 'Pipeline',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $rows = collect($roleIds)->map(fn (int $roleId) => [
            'role_id' => $roleId,
            'permission_id' => $newUserId,
        ])->all();

        if ($rows !== []) {
            DB::table('permission_role')->insert($rows);
        }
    }

    public function down(): void
    {
        $hr = DB::table('permissions')->where('key', 'screening.decide-hr')->first();
        $user = DB::table('permissions')->where('key', 'screening.decide-user')->first();

        if ($user) {
            $roleIds = DB::table('permission_role')->where('permission_id', $hr?->id ?? $user->id)->pluck('role_id')->all();
            DB::table('permission_role')->where('permission_id', $user->id)->delete();
            DB::table('permissions')->where('id', $user->id)->delete();
        }

        if ($hr) {
            DB::table('permissions')->where('id', $hr->id)->update([
                'key' => 'screening.decide',
                'label' => 'Putuskan skrining CV',
                'updated_at' => now(),
            ]);
        }
    }
};
