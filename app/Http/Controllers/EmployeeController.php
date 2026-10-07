<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Models\Unit;
use App\Support\Permissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $request->user()->requirePermission(Permissions::EMPLOYEE_VIEW);

        $employees = Employee::query()
            ->with('unit')
            ->when(
                $request->q,
                fn ($q, $search) => $q->where(function ($q) use ($search) {
                    $lower = strtolower($search);
                    $q->whereRaw('LOWER(nama_karyawan) LIKE ?', ["%{$lower}%"])
                        ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$lower}%"]);
                }),
            )
            ->when($request->unit, fn ($q, $unit) => $q->where('unit_id', $unit))
            ->when(
                $request->posisi,
                fn ($q, $posisi) => $q->where('posisi_pekerjaan', $posisi),
            )
            ->when(
                $request->profesi,
                fn ($q, $profesi) => $q->where('profesi', $profesi),
            )
            ->when(
                $request->jabatan,
                fn ($q, $jabatan) => $q->where('jabatan', $jabatan),
            )
            ->orderBy('nama_karyawan')
            ->paginate(15)
            ->withQueryString();

        $filters = [
            'units' => Unit::orderBy('nama')->get(['id', 'nama']),
            'posisi' => Employee::distinct()
                ->orderBy('posisi_pekerjaan')
                ->pluck('posisi_pekerjaan'),
            'profesi' => Employee::distinct()
                ->orderBy('profesi')
                ->pluck('profesi'),
            'jabatan' => Employee::distinct()
                ->orderBy('jabatan')
                ->pluck('jabatan'),
        ];

        return view('employees.index', compact('employees', 'filters'));
    }

    public function create(): View
    {
        auth()->user()->requirePermission(Permissions::EMPLOYEE_CREATE);

        return view('employees.create');
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse|Response
    {
        Employee::create($request->validated());

        if ($request->boolean('popup')) {
            return response('<script>window.close()</script>');
        }

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee): View
    {
        $user = auth()->user();

        if (! $user->hasPermission(Permissions::EMPLOYEE_VIEW)) {
            $user->requirePermission(Permissions::EMPLOYEE_VIEW_SELF);
            abort_unless($employee->user_id === $user->id, 403);
        }

        $employee->load('unit');

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        auth()->user()->requirePermission(Permissions::EMPLOYEE_UPDATE);

        $employee->load('unit');

        return view('employees.edit', compact('employee'));
    }

    public function update(
        UpdateEmployeeRequest $request,
        Employee $employee,
    ): RedirectResponse {
        $employee->update($request->validated());

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        auth()->user()->requirePermission(Permissions::EMPLOYEE_DELETE);

        $employee->delete();

        return redirect()
            ->route('karyawan.index')
            ->with('status', 'Data karyawan berhasil dihapus.');
    }
}
