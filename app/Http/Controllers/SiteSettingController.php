<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSiteSettingRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        Gate::authorize('viewAny', SiteSetting::class);

        $settings = SiteSetting::orderBy('id')->get()->keyBy('key');

        return view('site-settings.edit', compact('settings'));
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
