<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class DeleteStaffUsers extends Command
{
    protected $signature = 'user:delete-staff {--dry-run : Hanya tampilkan jumlah user yang akan dihapus}';

    protected $description = 'Hapus semua user dengan role staff (employee)';

    public function handle(): int
    {
        $roleIds = Role::where('key', Role::Employee)->pluck('id');

        if ($roleIds->isEmpty()) {
            $this->warn('Role employee tidak ditemukan. Tidak ada yang dihapus.');

            return self::SUCCESS;
        }

        $query = User::whereIn('role_id', $roleIds);
        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$count} user staff akan dihapus.");

            return self::SUCCESS;
        }

        if ($count === 0) {
            $this->info('Tidak ada user staff yang perlu dihapus.');

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        User::flushPermissionCache();

        $this->info("User staff dihapus: {$deleted}.");

        return self::SUCCESS;
    }
}
