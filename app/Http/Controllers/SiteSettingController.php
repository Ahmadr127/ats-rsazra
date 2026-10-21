<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use App\Services\CareerSeo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(CareerSeo $seo): View
    {
        Gate::authorize('viewAny', SiteSetting::class);

        $settings = SiteSetting::orderBy('id')->get()->keyBy('key');

        $tokenValues = $seo->publicTokenValues();
        $heroLede = $seo->hero((string) ($settings->get('hero_lede')?->value ?? ''), $tokenValues);
        $previewTitle = $seo->title((string) ($settings->get('seo_title')?->value ?? ''), $tokenValues);
        $previewDescription = $seo->description(
            (string) ($settings->get('seo_description')?->value ?? ''),
            $heroLede,
            $tokenValues,
        );

        $seoPreview = [
            'title' => $previewTitle,
            'description' => $previewDescription,
            'url' => route('home'),
            'tokens' => $tokenValues,
            'fallback_title' => CareerSeo::DEFAULT_TITLE,
            'default_hero' => CareerSeo::DEFAULT_HERO,
            'title_length' => Str::length($previewTitle),
            'description_length' => Str::length($previewDescription),
        ];

        return view('site-settings.edit', compact('settings', 'seoPreview'));
    }

    public function update(UpdateSiteSettingRequest $request): RedirectResponse
    {
        Gate::authorize('update', new SiteSetting);

        foreach ($request->validated()['settings'] as $key => $value) {
            SiteSetting::where('key', $key)->update(['value' => $value]);
        }

        SiteSetting::flush();

        return redirect()
            ->route('pengaturan-tampilan.edit')
            ->with('status', 'Tampilan halaman karier berhasil diperbarui.');
    }
}
