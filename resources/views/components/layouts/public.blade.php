@props([
    'title' => 'Karier - RS Azra',
    'mainClass' => 'w-full',
    'metaDescription' => null,
    'canonical' => null,
    'ogImage' => null,
    'ogType' => 'website',
])

@php
    $kontakTelepon = App\Models\SiteSetting::get('kontak_telepon', '(0251) 8382417');
    $kontakWaNomor = App\Models\SiteSetting::get('kontak_wa_nomor', '6281219801997');
    $kontakWaLabel = App\Models\SiteSetting::get('kontak_wa_label', 'WA 0812 1980 1997');
    $kontakEmail = App\Models\SiteSetting::get('kontak_email', 'rsazra@gmail.com');
    $kontakAlamat = App\Models\SiteSetting::get('kontak_alamat', 'Jl. Pintu Air No.1, Sempur, Bogor Tengah, Kota Bogor 16112');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @if ($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endif
    @if ($canonical)
        <link rel="canonical" href="{{ $canonical }}">
    @endif
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Karier RS Azra">
    <meta property="og:title" content="{{ $title }}">
    @if ($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endif
    @if ($canonical)
        <meta property="og:url" content="{{ $canonical }}">
    @endif
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">

    <div class="w-full bg-primary text-white">
        <div class="ui-shell flex items-center justify-between gap-4 py-1.5">
            <p class="text-[12px] font-medium tracking-wide">"Cepat, Ramah, Berkualitas"</p>
            <div class="flex items-center gap-4 text-[12px]">
                <span class="hidden sm:inline text-white/85">{{ $kontakTelepon }}</span>
                <a href="https://wa.me/{{ $kontakWaNomor }}" target="_blank" rel="noopener" class="text-white/85 hover:text-white">{{ $kontakWaLabel }}</a>
            </div>
        </div>
    </div>

    <header x-data="{ menuOpen: false }" class="sticky top-0 z-50 w-full border-b border-line bg-white">
        <div class="ui-shell flex h-14 items-center gap-3">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2.5">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="RS Azra"
                    class="h-9 w-9 shrink-0 object-contain"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'"
                >
                <span style="display:none;" class="h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary text-[14px] font-bold text-white">A</span>
                <span class="leading-tight">
                    <span class="block text-[14px] font-bold text-ink">RS Azra</span>
                    <span class="block text-[12px] font-medium text-ink-3">Karier</span>
                </span>
            </a>

            <nav class="ml-6 hidden items-center gap-1 lg:flex">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-[13px] font-semibold text-primary">Lowongan</a>
                <a href="https://rsazra.co.id/tentangkami" class="rounded-lg px-3 py-2 text-[13px] font-medium text-ink-2 hover:bg-paper hover:text-ink">Tentang Kami</a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <a href="{{ route('login') }}" class="ui-btn ui-btn-secondary py-2! text-[13px]">Login</a>
                <button
                    @click="menuOpen = !menuOpen"
                    class="rounded-lg p-2 text-ink-2 hover:bg-paper lg:hidden"
                    aria-label="Buka menu"
                >
                    <svg x-show="!menuOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="menuOpen" x-cloak class="border-t border-line bg-white lg:hidden">
            <nav class="ui-shell py-2">
                <a href="{{ route('home') }}" class="block border-b border-line-2 py-2.5 text-[14px] font-semibold text-primary">Lowongan</a>
                <a href="https://rsazra.co.id/tentangkami" class="block py-2.5 text-[14px] text-ink-2">Tentang Kami</a>
            </nav>
        </div>
    </header>

    <main class="{{ $mainClass }}">
        {{ $slot }}
    </main>

    <footer class="mt-10 w-full bg-[#0c1d1c] text-[#cfd6d3]">
        <div class="ui-shell grid gap-8 py-10 md:grid-cols-2">
            <div>
                <img src="{{ asset('images/logo.png') }}" alt="RS Azra" class="h-10 w-10 object-contain">
                <p class="mt-3 text-[14px] font-bold text-white">RS Azra Bogor</p>
                <p class="mt-2 max-w-xl text-[13px] leading-relaxed text-[#b8c0bd]">Layanan kesehatan 24 jam dengan dokter spesialis, IGD, rawat inap, dan fasilitas modern di Bogor.</p>
                <div class="mt-4 space-y-1.5 text-[13px] text-[#b8c0bd]">
                    <p>{{ $kontakAlamat }}</p>
                    <p><a href="tel:{{ preg_replace('/\D/', '', $kontakTelepon) }}" class="hover:text-white">{{ $kontakTelepon }}</a> &middot; <a href="mailto:{{ $kontakEmail }}" class="hover:text-white">{{ $kontakEmail }}</a></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="ui-label text-white/70!">Tautan</p>
                    <ul class="mt-3 space-y-2 text-[13px]">
                        <li><a href="{{ route('home') }}" class="font-semibold text-white">Karier</a></li>
                        <li><a href="https://rsazra.co.id/jadwal-dokter" class="hover:text-white">Jadwal Dokter</a></li>
                        <li><a href="https://rsazra.co.id/fasilitas" class="hover:text-white">Fasilitas</a></li>
                        <li><a href="https://rsazra.co.id/berita" class="hover:text-white">Berita</a></li>
                    </ul>
                </div>
                <div>
                    <p class="ui-label text-white/70!">Ikuti</p>
                    <ul class="mt-3 space-y-2 text-[13px]">
                        <li><a href="https://instagram.com/rsazra" target="_blank" rel="noopener" class="hover:text-white">Instagram</a></li>
                        <li><a href="https://facebook.com/rsazra" target="_blank" rel="noopener" class="hover:text-white">Facebook</a></li>
                        <li><a href="https://youtube.com/@rsazra" target="_blank" rel="noopener" class="hover:text-white">YouTube</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="ui-shell flex flex-wrap items-center justify-between gap-2 py-4 text-[12px] text-[#6c7773]">
                <span>Copyright &copy; {{ date('Y') }} RS Azra Group.</span>
                <span>KARIR &middot; RS AZRA BOGOR</span>
            </div>
        </div>
    </footer>

</body>
</html>
