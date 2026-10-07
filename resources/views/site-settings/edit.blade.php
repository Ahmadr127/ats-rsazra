<x-layouts.app title="Tampilan Karier - ATS RS Azra">

    <div class="mb-4">
        <h1 class="text-xl font-semibold text-gray-900">Tampilan Halaman Karier</h1>
        <p class="text-xs text-gray-500 mt-0.5">Ubah teks hero yang tampil di halaman utama karier tanpa menyentuh kode</p>
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
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Token dinamis paragraf</p>
                <div class="space-y-1.5">
                    @foreach ([
                        '{jumlah_posisi}' => 'Jumlah lowongan terbuka saat ini',
                        '{jumlah_unit}' => 'Jumlah unit yang membuka lowongan',
                        '{lowongan_terbaru}' => 'Tiga judul lowongan terbaru',
                    ] as $token => $desc)
                        <div>
                            <code class="text-[11px] font-mono text-primary bg-primary/5 px-1.5 py-0.5 rounded">{{ $token }}</code>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
