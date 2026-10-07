<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->timestamps();
        });

        $defaults = [
            'hr_admin' => 'Admin HR',
            'hr_manager' => 'Manajer HR',
            'unit_head' => 'Kepala Unit',
            'director' => 'Direktur',
            'employee' => 'Karyawan',
        ];

        foreach ($defaults as $key => $label) {
            DB::table('roles')->updateOrInsert(['key' => $key], ['label' => $label]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        DB::statement('UPDATE users SET role_id = (SELECT id FROM roles WHERE roles.key = users.role)');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::dropIfExists('permission_role');

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->nullable()->after('name');
        });

        DB::statement('UPDATE users SET role = (SELECT key FROM roles WHERE roles.id = users.role_id)');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('permission_role');

        Schema::create('permission_role', function (Blueprint $table) {
            $table->string('role');
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role', 'permission_id']);
        });

        Schema::dropIfExists('roles');
    }
};
