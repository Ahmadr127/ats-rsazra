<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {

        $roles = Role::withCount('users')->orderBy('label')->get();

        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {

        return view('roles.create');
    }

    public function store(): RedirectResponse
    {

        $validated = request()->validate([
            'key' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:roles,key'],
            'label' => ['required', 'string', 'max:100'],
        ]);

        Role::create($validated);
        User::flushPermissionCache();

        return redirect()
            ->route('pengaturan.peran.index')
            ->with('status', 'Peran berhasil dibuat. Atur permission-nya di halaman Hak Akses.');
    }

    public function edit(Role $peran): View
    {

        return view('roles.edit', ['role' => $peran]);
    }

    public function update(Role $peran): RedirectResponse
    {

        $validated = request()->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $peran->update($validated);
        User::flushPermissionCache();

        return redirect()
            ->route('pengaturan.peran.index')
            ->with('status', 'Peran berhasil diperbarui.');
    }

    public function destroy(Role $peran): RedirectResponse
    {

        abort_if($peran->users()->exists(), 422, 'Peran tidak dapat dihapus karena masih dipakai pengguna.');

        if ($this->isLastAccessHolder($peran)) {
            return back()->withErrors(['role' => 'Peran ini satu-satunya pemegang akses Hak Akses. Pindahkan dulu ke peran lain.']);
        }

        $peran->delete();
        User::flushPermissionCache();

        return redirect()
            ->route('pengaturan.peran.index')
            ->with('status', 'Peran berhasil dihapus.');
    }

    private function isLastAccessHolder(Role $peran): bool
    {
        $holds = $peran->permissions()
            ->whereIn('key', [Permissions::RBAC_MANAGE, Permissions::MENU_RBAC])
            ->count() === 2;

        if (! $holds) {
            return false;
        }

        return ! Role::whereKeyNot($peran->id)
            ->whereHas('permissions', fn ($q) => $q->where('key', Permissions::RBAC_MANAGE))
            ->whereHas('permissions', fn ($q) => $q->where('key', Permissions::MENU_RBAC))
            ->exists();
    }
}
