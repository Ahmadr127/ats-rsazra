<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class SimutuOrganisasiSeeder extends Seeder
{
    /**
     * Data sumber: backup PostgreSQL Simutu (sql/simutu_backup.sql),
     * diekstrak ke database/data/simutu_organisasi.json.
     *
     * Mapping role Simutu -> Role ATS:
     * - Administrator      -> HrAdmin
     * - Kepala unit/Kepala -> UnitHead (kepala departemen)
     * - lainnya            -> Employee
     */
    public function run(): void
    {
        $data = $this->loadData();

        $roles = collect($data['roles'])->keyBy('id');
        $unitIds = $this->seedUnits($data['units']);

        foreach ($data['users'] as $row) {
            /** @var array{nama_role: string, deskripsi_role: ?string} $role */
            $role = $roles->get((int) $row['role_id']);

            $user = User::updateOrCreate(
                ['username' => $row['username']],
                [
                    'name' => $row['nama_lengkap'],
                    'password' => $row['password'],
                    'role' => $this->mapRole((int) $row['role_id']),
                    'must_change_password' => false,
                    'is_active' => $row['status_user'] === 'aktif',
                ]
            );

            Employee::updateOrCreate(
                ['nip' => $row['nip']],
                [
                    'user_id' => $user->id,
                    'nama_karyawan' => $row['nama_lengkap'],
                    'unit_id' => $unitIds[(int) $row['unit_id']],
                    'posisi_pekerjaan' => $this->posisiPekerjaan($role),
                    'profesi' => $row['profesi'],
                    'jabatan' => $role['nama_role'],
                ]
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function loadData(): array
    {
        $path = database_path('data/simutu_organisasi.json');

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            throw new RuntimeException("Tidak dapat membaca {$path}");
        }

        return $decoded;
    }

    /**
     * @param  array<int, array{id: int|string, nama_unit: string}>  $units
     * @return array<int, int> id unit Simutu => id unit lokal
     */
    private function seedUnits(array $units): array
    {
        $existing = Unit::query()
            ->get()
            ->keyBy(fn (Unit $unit) => mb_strtolower($unit->nama));

        $ids = [];

        foreach ($units as $unit) {
            $key = mb_strtolower($unit['nama_unit']);
            $model = $existing->get($key);

            if (! $model instanceof Unit) {
                $model = Unit::create(['nama' => $unit['nama_unit']]);
                $existing->put($key, $model);
            }

            $ids[(int) $unit['id']] = $model->id;
        }

        return $ids;
    }

    private function mapRole(int $simutuRoleId): Role
    {
        return match ($simutuRoleId) {
            1 => Role::HrAdmin,
            8, 9 => Role::UnitHead,
            default => Role::Employee,
        };
    }

    /**
     * @param  array{nama_role: string, deskripsi_role: ?string}  $role
     */
    private function posisiPekerjaan(array $role): string
    {
        $deskripsi = trim((string) $role['deskripsi_role']);

        if ($deskripsi !== '' && mb_strlen($deskripsi) < 40) {
            return mb_substr($deskripsi, 0, 100);
        }

        return mb_substr($role['nama_role'], 0, 100);
    }
}
