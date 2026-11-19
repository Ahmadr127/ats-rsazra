<?php

namespace App\Http\Controllers;

use App\Actions\GetRecruitmentMetrics;
use App\Models\Application;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, GetRecruitmentMetrics $metrics): View
    {
        $user = auth()->user();

        $isOrgScope = $user->hasPermission(Permissions::DASHBOARD_VIEW_ORG);
        $isUnitScope = ! $isOrgScope && $user->hasPermission(Permissions::DASHBOARD_VIEW_UNIT);

        if (! $isUnitScope && ! $isOrgScope) {
            return view('dashboard', ['scope' => 'none', 'hasUnit' => false]);
        }

        $filters = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'unit_id' => 'nullable|integer|exists:units,id',
            'vacancy_id' => 'nullable|integer|exists:vacancies,id',
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $lockedUnit = null;
        $hasUnit = true;

        if ($isUnitScope) {
            $unitId = $user->employee?->unit_id;
            $hasUnit = $unitId !== null;

            // Force the user's own unit. A unit-tier user must never widen scope, so any
            // request unit_id is overridden. When the user has no unit, scope to a
            // non-existent id (never null — null means "no filter" = org-wide data).
            $filters['unit_id'] = $unitId ?? 0;
            $lockedUnit = $user->employee?->unit;
        }

        $applications = Application::query()
            ->with([
                'candidate:id,nama_lengkap',
                'vacancy:id,judul_posisi,unit_id',
                'vacancy.unit:id,nama',
                'stages:id,application_id,key,nama,status,position',
            ])
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($filters['vacancy_id'] ?? null, fn ($q, $id) => $q->where('vacancy_id', $id))
            ->when(
                $filters['unit_id'] ?? null,
                fn ($q, $id) => $q->whereHas('vacancy', fn ($vq) => $vq->where('unit_id', $id))
            )
            ->when($filters['search'] ?? null, function ($q, string $search): void {
                $term = '%'.mb_strtolower($search).'%';

                $q->where(function ($q) use ($term): void {
                    $q->whereHas('candidate', fn ($cq) => $cq->whereRaw('lower(nama_lengkap) like ?', [$term]))
                        ->orWhereHas('vacancy', function ($vq) use ($term): void {
                            $vq->whereRaw('lower(judul_posisi) like ?', [$term])
                                ->orWhereHas('unit', fn ($uq) => $uq->whereRaw('lower(nama) like ?', [$term]));
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('dashboard', [
            'scope' => $isUnitScope ? 'unit' : 'org',
            'lockedUnit' => $lockedUnit,
            'hasUnit' => $hasUnit,
            'filters' => $filters,
            'applications' => $applications,
            ...$metrics->execute($filters, includeUnitOptions: ! $isUnitScope),
        ]);
    }
}
