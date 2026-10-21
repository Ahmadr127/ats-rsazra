<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_page_shows_seo_fields(): void
    {
        $admin = User::factory()->hrAdmin()->create();

        $this->actingAs($admin)
            ->get(route('pengaturan-tampilan.edit'))
            ->assertOk()
            ->assertSee('Judul SEO')
            ->assertSee('Deskripsi SEO');
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

    public function test_career_page_uses_dynamic_seo_tokens(): void
    {
        SiteSetting::where('key', 'seo_title')->update([
            'value' => 'Lowongan {jumlah_posisi} di {jumlah_unit} Unit',
        ]);
        SiteSetting::where('key', 'seo_description')->update([
            'value' => 'Tersedia {jumlah_posisi} posisi di {jumlah_unit} unit, termasuk {lowongan_terbaru}.',
        ]);
        SiteSetting::flush();

        Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat ICU']);

        $response = $this->get(route('karier.index'));

        $response->assertOk();
        $response->assertSee('<title>Lowongan 1 di 1 Unit</title>', false);
        $response->assertSee('Tersedia 1 posisi di 1 unit, termasuk Perawat ICU.', false);
    }
}
