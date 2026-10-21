<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_only_lists_public_listing_urls(): void
    {
        $active = Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat Aktif']);
        $draft = Vacancy::factory()->create(['judul_posisi' => 'Draft', 'status' => VacancyStatus::Draft]);
        $closed = Vacancy::factory()->closed()->create(['judul_posisi' => 'Ditutup']);
        $expired = Vacancy::factory()->expired()->create(['judul_posisi' => 'Kedaluwarsa']);
        $withoutFlyer = Vacancy::factory()->published()->create(['judul_posisi' => 'Tanpa Flyer', 'flyer_path' => null]);

        $response = $this->get(route('sitemap'));

        $response->assertOk();
        $this->assertStringStartsWith('text/xml', (string) $response->headers->get('Content-Type'));
        $this->assertNotFalse(simplexml_load_string($response->getContent()));

        $response->assertSee('<loc>'.route('home').'</loc>', false);
        $response->assertSee('<loc>'.route('karier.show', $active).'</loc>', false);
        $response->assertDontSee('<loc>'.route('karier.index').'</loc>', false);
        $response->assertDontSee(route('karier.show', $draft), false);
        $response->assertDontSee(route('karier.show', $closed), false);
        $response->assertDontSee(route('karier.show', $expired), false);
        $response->assertDontSee(route('karier.show', $withoutFlyer), false);
    }
}
