<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        // Sitemap wajib berupa URL absolut. Dibuat dinamis agar mengikuti
        // domain yang dipakai (localhost, staging, maupun production).
        $content = implode("\n", [
            'User-agent: *',
            'Disallow:',
            '',
            'Sitemap: '.url('/sitemap.xml'),
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
