<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\SiteSetting;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    private function setSetting(string $key, ?string $value): void
    {
        SiteSetting::where('key', $key)->update(['value' => $value]);
        SiteSetting::flush();
    }

    public function test_edit_page_shows_seo_fields_and_preview(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $this->setSetting('seo_title', '{jumlah_posisi} lowongan di RS Azra');
        $this->setSetting('seo_description', 'Terbaru: {lowongan_terbaru}.');
        Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat ICU']);

        $this->actingAs($admin)
            ->get(route('pengaturan-tampilan.edit'))
            ->assertOk()
            ->assertSee('Judul SEO')
            ->assertSee('Deskripsi SEO')
            ->assertSee('Preview hasil pencarian')
            ->assertSee('id="seo-preview-title"', false)
            ->assertSee('1 lowongan di RS Azra')
            ->assertSee('Terbaru: Perawat ICU.')
            ->assertSee('Google dapat menampilkan');
    }

    public function test_hr_admin_can_update_seo_settings(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $settings = SiteSetting::pluck('value', 'key')->all();
        $settings['seo_title'] = 'Karier RS Azra - {jumlah_posisi} Posisi';
        $settings['seo_description'] = 'Tersedia {jumlah_posisi} lowongan di RS Azra.';

        $this->actingAs($admin)
            ->put(route('pengaturan-tampilan.update'), ['settings' => $settings])
            ->assertRedirect(route('pengaturan-tampilan.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'seo_title',
            'value' => 'Karier RS Azra - {jumlah_posisi} Posisi',
        ]);
        $this->assertDatabaseHas('site_settings', [
            'key' => 'seo_description',
            'value' => 'Tersedia {jumlah_posisi} lowongan di RS Azra.',
        ]);
    }

    public function test_seo_fields_can_be_emptied_to_use_fallbacks(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $settings = SiteSetting::pluck('value', 'key')->all();
        $settings['seo_title'] = '';
        $settings['seo_description'] = '';

        $this->actingAs($admin)
            ->put(route('pengaturan-tampilan.update'), ['settings' => $settings])
            ->assertRedirect(route('pengaturan-tampilan.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', ['key' => 'seo_title', 'value' => null]);
        $this->assertDatabaseHas('site_settings', ['key' => 'seo_description', 'value' => null]);
    }

    public function test_seo_uses_all_tokens_from_public_vacancies_only(): void
    {
        $unitA = Unit::factory()->create(['nama' => 'IGD']);
        $unitB = Unit::factory()->create(['nama' => 'Rawat Inap']);

        Vacancy::factory()->published()->create([
            'judul_posisi' => 'Perawat Lama',
            'unit_id' => $unitA->id,
            'published_at' => now()->subDays(5),
        ]);
        Vacancy::factory()->published()->create([
            'judul_posisi' => 'Perawat Baru',
            'unit_id' => $unitA->id,
            'published_at' => now()->subDays(1),
        ]);
        Vacancy::factory()->published()->create([
            'judul_posisi' => 'Dokter Baru',
            'unit_id' => $unitB->id,
            'published_at' => now()->subHours(2),
        ]);

        Vacancy::factory()->create(['judul_posisi' => 'Draft Tersembunyi', 'status' => VacancyStatus::Draft]);
        Vacancy::factory()->closed()->create(['judul_posisi' => 'Ditutup']);
        Vacancy::factory()->expired()->create(['judul_posisi' => 'Kedaluwarsa']);
        Vacancy::factory()->published()->create(['judul_posisi' => 'Tanpa Flyer', 'flyer_path' => null]);

        $this->setSetting('seo_title', '{jumlah_posisi} posisi di {jumlah_unit} unit');
        $this->setSetting('seo_description', 'Terbaru: {lowongan_terbaru}.');

        $response = $this->get(route('karier.index'));

        $response->assertOk();
        $response->assertSee('<title>3 posisi di 2 unit</title>', false);
        $response->assertSee('Terbaru: Dokter Baru, Perawat Baru dan Perawat Lama.', false);
        $response->assertDontSee('Draft Tersembunyi');
        $response->assertDontSee('Kedaluwarsa');
        $response->assertDontSee('Tanpa Flyer');
    }

    public function test_seo_falls_back_to_default_title_and_hero_when_empty(): void
    {
        $this->setSetting('seo_title', '');
        $this->setSetting('seo_description', '');
        $this->setSetting('hero_lede', 'Hero kustom {jumlah_posisi} untuk {jumlah_unit} unit.');
        Vacancy::factory()->published()->create();

        $response = $this->get(route('karier.index'));

        $response->assertSee('<title>Karier - RS Azra</title>', false);
        $response->assertSee('name="description" content="Hero kustom 1 untuk 1 unit."', false);
    }

    public function test_seo_uses_zero_and_latest_fallback_without_active_vacancies(): void
    {
        $this->setSetting('seo_title', '{jumlah_posisi} posisi di {jumlah_unit} unit');
        $this->setSetting('seo_description', 'Cek {lowongan_terbaru}.');

        Vacancy::factory()->expired()->create(['judul_posisi' => 'Kedaluwarsa']);
        Vacancy::factory()->create(['judul_posisi' => 'Draft', 'status' => VacancyStatus::Draft]);
        Vacancy::factory()->closed()->create(['judul_posisi' => 'Ditutup']);

        $response = $this->get(route('karier.index'));

        $response->assertSee('<title>0 posisi di 0 unit</title>', false);
        $response->assertSee('Cek lowongan terbaru.', false);
    }

    public function test_seo_text_is_escaped_from_html_and_javascript(): void
    {
        $this->setSetting('seo_title', '</title><script>alert(1)</script>');
        $this->setSetting('seo_description', '"><script>alert(2)</script>');

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('</title><script>alert(1)</script>', false);
        $response->assertDontSee('"><script>alert(2)</script>', false);
        $response->assertSee('&lt;/title&gt;&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertSee('&quot;&gt;&lt;script&gt;alert(2)&lt;/script&gt;', false);
    }

    public function test_recommended_length_is_a_guideline_not_a_hard_limit(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $settings = SiteSetting::pluck('value', 'key')->all();
        $settings['seo_title'] = str_repeat('A', 100);

        $this->actingAs($admin)
            ->put(route('pengaturan-tampilan.update'), ['settings' => $settings])
            ->assertRedirect(route('pengaturan-tampilan.edit'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'seo_title',
            'value' => str_repeat('A', 100),
        ]);
    }

    public function test_seo_title_over_database_limit_is_rejected(): void
    {
        $admin = User::factory()->hrAdmin()->create();
        $settings = SiteSetting::pluck('value', 'key')->all();
        $settings['seo_title'] = str_repeat('A', 256);

        $this->actingAs($admin)
            ->put(route('pengaturan-tampilan.update'), ['settings' => $settings])
            ->assertSessionHasErrors('settings.seo_title');
    }
}
