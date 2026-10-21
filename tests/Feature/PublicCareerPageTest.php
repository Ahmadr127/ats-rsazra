<?php

namespace Tests\Feature;

use App\Enums\EmploymentType;
use App\Enums\JenisPendidikan;
use App\Enums\VacancyStatus;
use App\Models\SiteSetting;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicCareerPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    // ── Career index ──────────────────────────────────────────────────────────

    public function test_public_career_page_is_accessible_without_login(): void
    {
        $response = $this->get(route('karier.index'));

        $response->assertStatus(200);
        $response->assertViewIs('career.index');
    }

    public function test_career_page_shows_published_non_expired_vacancies(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat ICU']);

        $response = $this->get(route('karier.index'));

        $response->assertSee('Perawat ICU');
    }

    public function test_career_page_shows_lulusan(): void
    {
        $this->seedStages();
        Vacancy::factory()->published()->create([
            'judul_posisi' => 'Perawat ICU',
            'lulusan' => JenisPendidikan::D3->value,
        ]);

        $response = $this->get(route('karier.index'));

        $response->assertSee('Pendidikan Minimal D3');
    }

    public function test_career_page_renders_flyer_image(): void
    {
        Storage::fake('public');
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->withGeneratedFlyer()->create(['judul_posisi' => 'Perawat ICU']);

        $response = $this->get(route('karier.index'));

        Storage::disk('public')->assertExists($vacancy->flyer_path);
        $response->assertSee($vacancy->flyer_path, false);
        $response->assertSee('alt="Flyer lowongan Perawat ICU', false);
    }

    public function test_career_page_hides_draft_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->create([
            'judul_posisi' => 'Dokter Tersembunyi',
            'status' => VacancyStatus::Draft,
        ]);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Dokter Tersembunyi');
    }

    public function test_career_page_hides_closed_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->closed()->create(['judul_posisi' => 'Posisi Ditutup']);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Posisi Ditutup');
    }

    public function test_career_page_hides_expired_published_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->expired()->create(['judul_posisi' => 'Posisi Kedaluwarsa']);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Posisi Kedaluwarsa');
    }

    // ── Career show ───────────────────────────────────────────────────────────

    public function test_vacancy_detail_page_accessible_for_published_vacancy(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create(['judul_posisi' => 'Bidan Senior']);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('career.show');
        $response->assertSee('Bidan Senior');
    }

    public function test_vacancy_detail_shows_lulusan(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create([
            'lulusan' => JenisPendidikan::D4AtauS1->value,
        ]);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(200);
        $response->assertSee('Pendidikan Minimal D4/S1');
        $response->assertSee('Pendidikan Minimal:');
    }

    public function test_vacancy_detail_shows_apply_button(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertSee('Lamar Sekarang');
    }

    public function test_draft_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }

    public function test_closed_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->closed()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }

    public function test_expired_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->expired()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }

    public function test_career_index_has_stable_canonical_open_graph_and_robots(): void
    {
        SiteSetting::where('key', 'seo_title')->update(['value' => 'SEO Karier']);
        SiteSetting::where('key', 'seo_description')->update(['value' => 'Deskripsi SEO karier.']);
        SiteSetting::flush();

        Vacancy::factory()->published()->create();

        $response = $this->get(route('karier.index'));

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.route('home').'">', false);
        $response->assertSee('<meta property="og:url" content="'.route('home').'">', false);
        $response->assertSee('<meta property="og:title" content="SEO Karier">', false);
        $response->assertSee('<meta property="og:description" content="Deskripsi SEO karier.">', false);
        $response->assertSee('<meta property="og:image" content="'.asset('images/logo.png').'">', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">', false);
    }

    public function test_career_detail_has_canonical_open_graph_and_job_posting(): void
    {
        $vacancy = Vacancy::factory()->published()->create([
            'judul_posisi' => 'Perawat ICU',
            'jenis_pekerjaan' => EmploymentType::FullTime,
            'deskripsi_pekerjaan' => 'Merawat pasien di ICU.',
            'kualifikasi' => 'D3 Keperawatan.',
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertOk();
        $response->assertSee('<link rel="canonical" href="'.route('karier.show', $vacancy).'">', false);
        $response->assertSee('<meta property="og:type" content="article">', false);
        $response->assertSee('<meta property="og:url" content="'.route('karier.show', $vacancy).'">', false);
        $response->assertSee('<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"@type":"JobPosting"', false);
        $response->assertSee('"title":"Perawat ICU"', false);
        $response->assertSee('"employmentType":"FULL_TIME"', false);
        $response->assertSee('"directApply":true', false);
        $response->assertSee('"datePosted":"', false);
        $response->assertSee('"validThrough":"', false);
    }

    public function test_job_posting_is_not_rendered_on_career_index(): void
    {
        Vacancy::factory()->published()->create();

        $response = $this->get(route('karier.index'));

        $response->assertSee('"@type":"Organization"', false);
        $response->assertDontSee('"@type":"JobPosting"', false);
        $response->assertDontSee('"@type":"BreadcrumbList"', false);
    }

    public function test_filtered_career_page_is_noindex(): void
    {
        $response = $this->get(route('home', ['q' => 'perawat']));

        $response->assertOk();
        $response->assertSee('<meta name="robots" content="noindex,follow">', false);
    }

    public function test_structured_data_escapes_closing_script_tags(): void
    {
        $vacancy = Vacancy::factory()->published()->create([
            'judul_posisi' => 'Perawat </script><script>alert(1)</script>',
        ]);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertOk();
        $response->assertDontSee('</script><script>alert(1)</script>', false);
        $response->assertSee('\\u003C/script\\u003E', false);
    }

    public function test_career_index_query_count_is_bounded(): void
    {
        Vacancy::factory()->count(8)->published()->create();

        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $this->get(route('karier.index'))->assertOk();

        $this->assertLessThanOrEqual(10, $queries);
    }

    public function test_published_at_is_recorded_when_a_vacancy_goes_live(): void
    {
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $this->assertNull($vacancy->published_at);

        $vacancy->update(['status' => VacancyStatus::Published]);

        $this->assertNotNull($vacancy->fresh()->published_at);
    }
}
