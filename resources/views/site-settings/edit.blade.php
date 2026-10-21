<x-layouts.app title="Tampilan Karier - ATS RS Azra">

    <div class="mb-4">
        <h1 class="text-xl font-semibold text-gray-900">Tampilan Halaman Karier</h1>
        <p class="text-xs text-gray-500 mt-0.5">Ubah teks hero, SEO, dan kontak halaman karier tanpa menyentuh kode</p>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3">
            <p class="text-xs text-green-700">{{ session('status') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2">
            <div class="bg-white/80 border border-gray-200 rounded-md">
                <form method="POST" action="{{ route('pengaturan-tampilan.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="px-4 pt-4 pb-5 space-y-4">
                        <div>
                            <label for="hero_eyebrow" class="block text-xs font-medium text-gray-700 mb-1">
                                Teks kecil hero <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="hero_eyebrow"
                                name="settings[hero_eyebrow]"
                                value="{{ old('settings.hero_eyebrow', $settings['hero_eyebrow']?->value ?? '') }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.hero_eyebrow') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('settings.hero_eyebrow')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hero_title" class="block text-xs font-medium text-gray-700 mb-1">
                                Judul hero <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="hero_title"
                                name="settings[hero_title]"
                                value="{{ old('settings.hero_title', $settings['hero_title']?->value ?? '') }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.hero_title') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('settings.hero_title')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="hero_lede" class="block text-xs font-medium text-gray-700 mb-1">
                                Paragraf hero <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="hero_lede"
                                name="settings[hero_lede]"
                                rows="4"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('settings.hero_lede') border-red-400 @else border-gray-200 @enderror"
                            >{{ old('settings.hero_lede', $settings['hero_lede']?->value ?? '') }}</textarea>
                            @error('settings.hero_lede')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="border-t border-gray-200">

                    <div class="px-4 pt-4 pb-5 space-y-4">
                        <div>
                            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">SEO Halaman Karier</p>
                            <p class="mt-1 text-[11px] text-gray-500">Mendukung token dinamis yang sama dengan paragraf hero.</p>
                        </div>

                        <div>
                            <label for="seo_title" class="block text-xs font-medium text-gray-700 mb-1">
                                Judul SEO
                            </label>
                            <input
                                type="text"
                                id="seo_title"
                                name="settings[seo_title]"
                                value="{{ old('settings.seo_title', $settings->get('seo_title')?->value ?? '') }}"
                                maxlength="255"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.seo_title') border-red-400 @else border-gray-200 @enderror"
                            >
                            <p class="mt-1 text-[11px] text-gray-500">
                                Kosongkan untuk memakai default “Karier - RS Azra”. Tampil sebagai judul tab browser, og:title, dan hasil pencarian.
                                <span id="seo-title-count-field" class="font-medium text-gray-700">{{ $seoPreview['title_length'] }} karakter</span>
                                <span class="text-gray-400">· panduan 50–60</span>
                            </p>
                            @error('settings.seo_title')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="seo_description" class="block text-xs font-medium text-gray-700 mb-1">
                                Deskripsi SEO
                            </label>
                            <textarea
                                id="seo_description"
                                name="settings[seo_description]"
                                rows="3"
                                maxlength="500"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('settings.seo_description') border-red-400 @else border-gray-200 @enderror"
                            >{{ old('settings.seo_description', $settings->get('seo_description')?->value ?? '') }}</textarea>
                            <p class="mt-1 text-[11px] text-gray-500">
                                Kosongkan untuk memakai paragraf hero. Tampil sebagai meta description dan og:description.
                                <span id="seo-description-count-field" class="font-medium text-gray-700">{{ $seoPreview['description_length'] }} karakter</span>
                                <span class="text-gray-400">· panduan 150–160</span>
                            </p>
                            @error('settings.seo_description')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <hr class="border-t border-gray-200">

                    <div class="px-4 pt-4 pb-5 space-y-4">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Kontak &amp; Alamat</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="kontak_telepon" class="block text-xs font-medium text-gray-700 mb-1">
                                    Nomor telepon <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="kontak_telepon"
                                    name="settings[kontak_telepon]"
                                    value="{{ old('settings.kontak_telepon', $settings['kontak_telepon']?->value ?? '') }}"
                                    placeholder="(0251) 8382417"
                                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.kontak_telepon') border-red-400 @else border-gray-200 @enderror"
                                >
                                @error('settings.kontak_telepon')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="kontak_email" class="block text-xs font-medium text-gray-700 mb-1">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="kontak_email"
                                    name="settings[kontak_email]"
                                    value="{{ old('settings.kontak_email', $settings['kontak_email']?->value ?? '') }}"
                                    placeholder="rsazra@gmail.com"
                                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.kontak_email') border-red-400 @else border-gray-200 @enderror"
                                >
                                @error('settings.kontak_email')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="kontak_wa_nomor" class="block text-xs font-medium text-gray-700 mb-1">
                                    Nomor WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="kontak_wa_nomor"
                                    name="settings[kontak_wa_nomor]"
                                    value="{{ old('settings.kontak_wa_nomor', $settings['kontak_wa_nomor']?->value ?? '') }}"
                                    placeholder="6281219801997"
                                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.kontak_wa_nomor') border-red-400 @else border-gray-200 @enderror"
                                >
                                @error('settings.kontak_wa_nomor')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="kontak_wa_label" class="block text-xs font-medium text-gray-700 mb-1">
                                    Teks WhatsApp <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="kontak_wa_label"
                                    name="settings[kontak_wa_label]"
                                    value="{{ old('settings.kontak_wa_label', $settings['kontak_wa_label']?->value ?? '') }}"
                                    placeholder="WA 0812 1980 1997"
                                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('settings.kontak_wa_label') border-red-400 @else border-gray-200 @enderror"
                                >
                                @error('settings.kontak_wa_label')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="kontak_alamat" class="block text-xs font-medium text-gray-700 mb-1">
                                Alamat <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="kontak_alamat"
                                name="settings[kontak_alamat]"
                                rows="2"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('settings.kontak_alamat') border-red-400 @else border-gray-200 @enderror"
                            >{{ old('settings.kontak_alamat', $settings['kontak_alamat']?->value ?? '') }}</textarea>
                            @error('settings.kontak_alamat')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                        <button
                            type="submit"
                            class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
            <div class="bg-white/80 border border-gray-200 rounded-md p-4">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Token dinamis</p>
                <p class="text-[11px] text-gray-500 mb-3">Berlaku untuk paragraf hero, judul SEO, dan deskripsi SEO.</p>
                <div class="space-y-1.5">
                    @foreach ([
                        '{jumlah_posisi}' => 'Jumlah lowongan aktif yang tampil di halaman karier.',
                        '{jumlah_unit}' => 'Jumlah unit berbeda yang memiliki lowongan aktif.',
                        '{lowongan_terbaru}' => 'Maksimal tiga judul lowongan aktif terbaru. Jika kosong, memakai “lowongan terbaru”.',
                    ] as $token => $desc)
                        <div>
                            <code class="text-[11px] font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $token }}</code>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 bg-white/80 border border-gray-200 rounded-md p-4">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Preview hasil pencarian</p>
                <p class="text-[11px] text-gray-500 mb-3">Ilustrasi berdasarkan token yang sedang aktif.</p>
                <div class="rounded border border-gray-200 bg-white p-3">
                    <p id="seo-preview-url" class="truncate text-[11px] text-green-700">{{ $seoPreview['url'] }}</p>
                    <p id="seo-preview-title" class="mt-1 line-clamp-2 text-[14px] font-medium leading-snug text-blue-700">{{ $seoPreview['title'] }}</p>
                    <p id="seo-preview-description" class="mt-1 line-clamp-3 text-[12px] leading-relaxed text-gray-600">{{ $seoPreview['description'] }}</p>
                </div>
                <div class="mt-2 flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                    <span id="seo-title-count" class="text-gray-500">{{ $seoPreview['title_length'] }} karakter · panduan 50–60</span>
                    <span id="seo-description-count" class="text-gray-500">{{ $seoPreview['description_length'] }} karakter · panduan 150–160</span>
                </div>
                <p class="mt-2 text-[11px] text-gray-500">Pratinjau ini hanya ilustrasi. Google dapat menampilkan judul atau deskripsi yang berbeda.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const titleInput = document.getElementById('seo_title');
            const descriptionInput = document.getElementById('seo_description');
            const heroInput = document.getElementById('hero_lede');
            const previewTitle = document.getElementById('seo-preview-title');
            const previewDescription = document.getElementById('seo-preview-description');
            const previewUrl = document.getElementById('seo-preview-url');
            const titleCount = document.getElementById('seo-title-count-field');
            const previewTitleCount = document.getElementById('seo-title-count');
            const descriptionCount = document.getElementById('seo-description-count-field');
            const previewDescriptionCount = document.getElementById('seo-description-count');

            if (!titleInput || !descriptionInput || !previewTitle || !previewDescription) {
                return;
            }

            const tokens = @json($seoPreview['tokens']);
            const fallbackTitle = @json($seoPreview['fallback_title']);
            const defaultHero = @json($seoPreview['default_hero']);
            const previewUrlValue = @json($seoPreview['url']);
            const tokenPattern = /\{(?:jumlah_posisi|jumlah_unit|lowongan_terbaru)\}/g;

            const applyTokens = (value) => value.replace(tokenPattern, (token) => {
                const key = token.slice(1, -1);
                return Object.prototype.hasOwnProperty.call(tokens, key) ? String(tokens[key]) : token;
            });

            const resolve = (value, fallback) => {
                const template = (value || '').trim();

                return applyTokens(template !== '' ? template : fallback);
            };

            const setCount = (element, value, min, max) => {
                const length = Array.from(value).length;
                const inRange = length >= min && length <= max;

                element.textContent = `${length} karakter · panduan ${min}–${max}`;
                element.classList.toggle('text-amber-600', !inRange);
                element.classList.toggle('text-gray-500', inRange);
            };

            const updatePreview = () => {
                const heroLede = resolve(heroInput?.value || '', defaultHero);
                const title = resolve(titleInput.value, fallbackTitle);
                const descriptionTemplate = (descriptionInput.value || '').trim();
                const description = descriptionTemplate !== '' ? applyTokens(descriptionTemplate) : heroLede;

                previewUrl.textContent = previewUrlValue;
                previewTitle.textContent = title;
                previewDescription.textContent = description;
                setCount(titleCount, title, 50, 60);
                setCount(previewTitleCount, title, 50, 60);
                setCount(descriptionCount, description, 150, 160);
                setCount(previewDescriptionCount, description, 150, 160);
            };

            [titleInput, descriptionInput, heroInput].filter(Boolean).forEach((input) => {
                input.addEventListener('input', updatePreview);
            });

            updatePreview();
        });
    </script>

</x-layouts.app>
