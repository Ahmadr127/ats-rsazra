<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Enums\VacancyStatus;
use App\Models\SiteSetting;
use App\Models\Unit;
use App\Models\Vacancy;
use App\Services\CareerSeo;
use Illuminate\View\View;

class CareerController extends Controller
{
    public function index(CareerSeo $seo): View
    {
        $q = trim((string) request('q', ''));
        $unitFilter = array_filter(array_map('intval', (array) request('unit', [])));
        $typeFilter = array_filter((array) request('type', []));

        $query = Vacancy::with('unit')->publiclyListed();

        if ($q !== '') {
            $escaped = addcslashes($q, '%_\\');
            $query->whereRaw('LOWER(judul_posisi) LIKE ?', ['%'.strtolower($escaped).'%']);
        }

        if (! empty($unitFilter)) {
            $query->whereIn('unit_id', $unitFilter);
        }

        if (! empty($typeFilter)) {
            $query->whereIn('jenis_pekerjaan', $typeFilter);
        }

        $vacancies = (clone $query)
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->paginate(8)
            ->withQueryString();

        // Satu agregasi dipakai untuk total posisi sekaligus jumlah per jenis.
        $typeCounts = Vacancy::query()->publiclyListed()
            ->selectRaw('jenis_pekerjaan, count(*) as count')
            ->groupBy('jenis_pekerjaan')
            ->pluck('count', 'jenis_pekerjaan');
        $totalRoles = (int) $typeCounts->sum();

        $units = Unit::whereHas('vacancies', fn ($q) => $q->publiclyListed())
            ->withCount(['vacancies as published_count' => fn ($q) => $q->publiclyListed()])
            ->orderBy('nama')
            ->get();

        $latestTitles = Vacancy::query()->publiclyListed()
            ->orderByRaw('COALESCE(published_at, created_at) DESC')
            ->limit(3)
            ->pluck('judul_posisi');

        $employmentTypes = EmploymentType::cases();
        $tokenValues = $seo->values($totalRoles, $units->count(), $latestTitles);

        $heroEyebrow = SiteSetting::get('hero_eyebrow', 'RS Azra · Karier');
        $heroTitle = SiteSetting::get('hero_title', 'Bergabung dalam karya penyembuhan yang bermakna.');
        $heroLede = $seo->hero((string) SiteSetting::get('hero_lede', ''), $tokenValues);
        $seoTitle = $seo->title((string) SiteSetting::get('seo_title', ''), $tokenValues);
        $seoDescription = $seo->description(
            (string) SiteSetting::get('seo_description', ''),
            $heroLede,
            $tokenValues,
        );

        $isFiltered = $q !== '' || ! empty($unitFilter) || ! empty($typeFilter);
        $robots = ($isFiltered || $vacancies->currentPage() > 1)
            ? 'noindex,follow'
            : 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1';

        return view('career.index', compact(
            'vacancies',
            'totalRoles',
            'units',
            'typeCounts',
            'employmentTypes',
            'unitFilter',
            'typeFilter',
            'heroEyebrow',
            'heroTitle',
            'heroLede',
            'seoTitle',
            'seoDescription',
            'robots',
        ));
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
}
