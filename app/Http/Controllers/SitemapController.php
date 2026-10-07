<?php

namespace App\Http\Controllers;

use App\Models\Vacancy;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $vacancies = Vacancy::published()->whereNotNull('flyer_path')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        return response()
            ->view('sitemap', compact('vacancies'))
            ->header('Content-Type', 'text/xml');
    }
}
