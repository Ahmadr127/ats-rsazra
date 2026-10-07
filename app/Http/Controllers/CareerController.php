<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\SiteSetting;
use App\Models\Unit;
use App\Models\Vacancy;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(): View
    {
        $q = trim((string) request('q', ''));
        $unitFilter = array_filter(array_map('intval', (array) request('unit', [])));
        $typeFilter = array_filter((array) request('type', []));

        $query = Vacancy::with('unit')->published()->whereNotNull('flyer_path');

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->where('judul_posisi', 'ilike', "%{$escaped}%");
        }

        if (! empty($unitFilter)) {
            $query->whereIn('unit_id', $unitFilter);
        }

        if (! empty($typeFilter)) {
            $query->whereIn('jenis_pekerjaan', $typeFilter);
        }

        $vacancies = $query->orderByDesc('created_at')->paginate(8)->withQueryString();

        $totalRoles = Vacancy::published()->whereNotNull('flyer_path')->count();

        $units = Unit::whereHas('vacancies', fn ($q) => $q->published()->whereNotNull('flyer_path'))
            ->withCount(['vacancies as published_count' => fn ($q) => $q->published()->whereNotNull('flyer_path')])
            ->orderBy('nama')
            ->get();

        $typeCounts = Vacancy::published()->whereNotNull('flyer_path')
            ->selectRaw('jenis_pekerjaan, count(*) as count')
            ->groupBy('jenis_pekerjaan')
            ->pluck('count', 'jenis_pekerjaan');

        $employmentTypes = EmploymentType::cases();

        $slides = Vacancy::with('unit')->published()->whereNotNull('flyer_path')
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $heroEyebrow = SiteSetting::get('hero_eyebrow', 'RS Azra · Karier');
        $heroTitle = SiteSetting::get('hero_title', 'Bergabung dalam karya penyembuhan yang bermakna.');
        $heroLede = $this->composeLede(
            SiteSetting::get('hero_lede', ''),
            $totalRoles,
            $units->count()
        );
        $seoTitle = "Lowongan Kerja RS Azra — {$totalRoles} Posisi Terbuka";
        $seoDescription = $heroLede;

        return view('career.index', compact('vacancies', 'totalRoles', 'units', 'typeCounts', 'employmentTypes', 'unitFilter', 'typeFilter', 'slides', 'heroEyebrow', 'heroTitle', 'heroLede', 'seoTitle', 'seoDescription'));
    }

    public function show(Vacancy $vacancy): View
    {
        abort_unless(
            $vacancy->status === VacancyStatus::Published
            && $vacancy->tenggat_lamaran->gte(now()->startOfDay()),
            404,
        );

        $vacancy->load('unit', 'workflowTemplateSnapshot');

        return view('career.show', compact('vacancy'));
    }

    /**
     * Susun paragraf hero dari template admin dengan token dinamis:
     * {jumlah_posisi}, {jumlah_unit}, {lowongan_terbaru}.
     */
    private function composeLede(string $template, int $totalRoles, int $unitCount): string
    {
        if (trim($template) === '') {
            $template = 'Jelajahi {jumlah_posisi} posisi terbuka di {jumlah_unit} unit RS Azra, termasuk {lowongan_terbaru}. Pilih lowongan, baca detailnya, dan lamar langsung dari halaman ini.';
        }

        $latest = Vacancy::published()->whereNotNull('flyer_path')
            ->orderByDesc('created_at')
            ->limit(3)
            ->pluck('judul_posisi');

        return str_replace(
            ['{jumlah_posisi}', '{jumlah_unit}', '{lowongan_terbaru}'],
            [(string) $totalRoles, (string) $unitCount, $latest->join(', ', ' dan ')],
            $template
        );
    }
}
