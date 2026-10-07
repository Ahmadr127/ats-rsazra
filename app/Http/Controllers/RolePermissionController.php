<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function index(): View
    {
        $grouped = Permission::orderBy('id')->get()->groupBy('group');
        $roles = Role::orderBy('label')->get();

        $granted = DB::table('permission_role')->get()
            ->groupBy('role_id')
            ->map(fn ($rows) => $rows->pluck('permission_id')->all())
            ->all();

        return view('hak-akses.index', compact('grouped', 'roles', 'granted'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['nullable', 'array'],
            'permissions.*.*' => ['integer', 'exists:permissions,id'],
        ]);

        $matrix = $validated['permissions'] ?? [];
        $roles = Role::all()->keyBy('id');

        foreach (array_keys($matrix) as $roleId) {
            abort_unless($roles->has((int) $roleId), 422, 'Peran tidak dikenal.');
        }

        $keysById = Permission::pluck('key', 'id');

        // Lockout guard, role-agnostic: after saving, at least one role must
        // still hold both permissions that grant access to this page.
        $hasAdmin = false;

        foreach ($roles as $role) {
            $keys = collect($matrix[$role->id] ?? [])
                ->map(fn (int $id) => $keysById->get($id))
                ->all();

            if (in_array(Permissions::RBAC_MANAGE, $keys, true)
                && in_array(Permissions::MENU_RBAC, $keys, true)) {
                $hasAdmin = true;

                break;
            }
        }

        if (! $hasAdmin) {
            return back()->withErrors(['permissions' => 'Minimal satu peran wajib memiliki akses Hak Akses agar tidak terkunci.']);
        }

        DB::transaction(function () use ($matrix, $roles): void {
            foreach ($roles as $role) {
                $role->permissions()->sync(array_unique($matrix[$role->id] ?? []));
            }
        });

        User::flushPermissionCache();

        return redirect()
            ->route('pengaturan.hak-akses.index')
            ->with('status', 'Matriks hak akses berhasil disimpan.');
    }
}
