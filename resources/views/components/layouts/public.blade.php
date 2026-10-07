@props(['title' => 'Karier - RS Azra', 'mainClass' => 'w-full flex-1'])

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --secondary: #65a30d;
            --dark: #0f172a;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
        }
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { color: var(--gray-800); background: var(--gray-50); }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 4px 12px rgba(0,0,0,0.04);
            border: 1px solid var(--gray-100);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }
        .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.12), 0 8px 24px rgba(0,0,0,0.06);
        }
        .card-interactive:hover { transform: translateY(-2px); }

        /* Button Styles */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
            transition: all 0.2s ease; cursor: pointer; border: none;
        }
        .btn-primary {
            background: var(--primary); color: white;
        }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline {
            background: white; color: var(--gray-700); border: 1.5px solid var(--gray-200);
        }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
        .btn-ghost {
            background: transparent; color: var(--gray-600);
        }
        .btn-ghost:hover { background: var(--gray-100); color: var(--gray-800); }

        /* Badge Styles */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 9999px; font-size: 12px; font-weight: 600;
        }
        .badge-primary { background: var(--primary-light); color: var(--primary-dark); }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }

        /* Form Styles */
        .form-input {
            width: 100%; padding: 10px 14px; border-radius: 10px;
            border: 1.5px solid var(--gray-200); font-size: 14px;
            background: white; color: var(--gray-800);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .form-input:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }
        .form-input::placeholder { color: var(--gray-400); }
        .form-label {
            display: block; font-size: 13px; font-weight: 600;
            color: var(--gray-700); margin-bottom: 6px;
        }

        /* Section Styles */
        .section { padding: 48px 0; }
        .section-header {
            text-align: center; max-width: 640px; margin: 0 auto 40px;
        }
        .section-title {
            font-size: 28px; font-weight: 800; color: var(--gray-900); letter-spacing: -0.02em;
        }
        .section-subtitle {
            font-size: 15px; color: var(--gray-500); margin-top: 8px; line-height: 1.6;
        }

        /* Navbar */
        .nav-link {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; font-size: 14px; font-weight: 500;
            color: var(--gray-600); border-radius: 8px;
            transition: all 0.15s ease; white-space: nowrap;
        }
        .nav-link:hover { background: var(--gray-100); color: var(--gray-900); }
        .nav-link.active { color: var(--primary); background: var(--primary-light); }

        /* Footer */
        .footer-link {
            color: var(--gray-400); font-size: 14px;
            transition: color 0.15s ease; text-decoration: none;
        }
        .footer-link:hover { color: white; }

        /* Utility */
        .container-public {
            width: 100%; max-width: 1280px; margin: 0 auto;
            padding-left: 24px; padding-right: 24px;
        }
        @media (min-width: 1024px) {
            .container-public { padding-left: 48px; padding-right: 48px; }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    {{-- Top Bar --}}
    <div class="bg-teal-700 text-white text-sm">
        <div class="container-public py-2.5 flex items-center justify-between gap-4">
            <span class="hidden sm:block text-white/80 text-xs">RS Azra Bogor — Melayani dengan hati sejak 1994</span>
            <div class="flex items-center gap-4 ml-auto">
                <span class="text-white/80 text-xs">(0251) 8382417</span>
                <div class="flex items-center gap-2">
                    <a href="https://instagram.com/rsazra" target="_blank" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                    </a>
                    <a href="https://facebook.com/rsazra" target="_blank" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://wa.me/6281219801997" target="_blank" class="text-white/60 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <header x-data="{ menuOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="container-public flex h-16 items-center justify-between gap-4">
            {{-- Logo --}}
            <a href="{{ route('karier.index') }}" class="flex items-center gap-3 shrink-0">
                <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white font-bold text-lg">A</div>
                <div class="flex flex-col">
                    <span class="text-lg font-bold text-gray-900 leading-tight">RS Azra</span>
                    <span class="text-[10px] text-teal-600 font-semibold uppercase tracking-wider">Karir & Rekrutmen</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
                <a href="{{ route('karier.index') }}" class="nav-link {{ request()->routeIs('karier.index') ? 'active' : '' }}">Lowongan</a>
                <a href="{{ route('karier.index') }}#proses" class="nav-link">Proses Rekrutmen</a>
                <a href="{{ route('login') }}" class="nav-link">Masuk Pelamar</a>
            </nav>

            {{-- CTA --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="hidden lg:inline-flex btn btn-ghost text-sm">Login</a>
                <a href="{{ route('karier.index') }}#lowongan" class="btn btn-primary text-sm">Lamar Sekarang</a>
                <button @click="menuOpen = !menuOpen" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg x-show="!menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="menuOpen" x-transition class="lg:hidden border-t border-gray-100 bg-white">
            <nav class="container-public py-3 space-y-1">
                <a href="{{ route('karier.index') }}" class="block px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('karier.index') ? 'bg-teal-50 text-teal-700' : 'text-gray-700 hover:bg-gray-50' }}">Lowongan</a>
                <a href="{{ route('karier.index') }}#proses" class="block px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Proses Rekrutmen</a>
                <a href="{{ route('login') }}" class="block px-4 py-3 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Masuk Pelamar</a>
                <div class="pt-2 pb-1">
                    <a href="{{ route('karier.index') }}#lowongan" class="btn btn-primary w-full">Lamar Sekarang</a>
                </div>
            </nav>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="{{ $mainClass }}">
        {{ $slot }}
    </main>

    @stack('scripts')

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300">
        <div class="container-public py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
                {{-- Brand --}}
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-teal-600 flex items-center justify-center text-white font-bold text-lg">A</div>
                        <div>
                            <span class="text-white font-bold text-lg block leading-tight">RS Azra</span>
                            <span class="text-teal-400 text-xs font-medium">Bogor</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed mb-6">
                        Rumah Sakit AZRA Bogor menyediakan layanan kesehatan lengkap 24 jam dengan dokter spesialis dan fasilitas modern.
                    </p>
                    <div class="flex items-center gap-3">
                        <a href="https://instagram.com/rsazra" target="_blank" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-teal-600 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                        </a>
                        <a href="https://facebook.com/rsazra" target="_blank" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-teal-600 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://youtube.com/@rsazra" target="_blank" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-teal-600 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>
                        </a>
                        <a href="https://wa.me/6281219801997" target="_blank" class="w-9 h-9 rounded-lg bg-gray-800 flex items-center justify-center text-gray-400 hover:bg-teal-600 hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        </a>
                    </div>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Navigasi</h4>
                    <ul class="space-y-3">
                        <li><a href="{{ route('karier.index') }}" class="footer-link">Lowongan Kerja</a></li>
                        <li><a href="{{ route('karier.index') }}#proses" class="footer-link">Proses Rekrutmen</a></li>
                        <li><a href="{{ route('login') }}" class="footer-link">Masuk Pelamar</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <svg class="w-4 h-4 mt-0.5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                            Jl. Pintu Air No.1, Sempur, Bogor Tengah
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <svg class="w-4 h-4 mt-0.5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                            (0251) 8382417
                        </li>
                        <li class="flex items-start gap-3 text-sm text-gray-400">
                            <svg class="w-4 h-4 mt-0.5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                            rekrutmen@rsazra.co.id
                        </li>
                    </ul>
                </div>

                {{-- Newsletter --}}
                <div>
                    <h4 class="text-white font-semibold text-sm mb-4">Kabar Terbaru</h4>
                    <p class="text-sm text-gray-400 mb-4">Dapatkan informasi lowongan terbaru langsung ke email Anda.</p>
                    <form class="flex gap-0" onsubmit="return false;">
                        <input type="email" placeholder="Email Anda" class="flex-1 bg-gray-800 border border-gray-700 rounded-l-lg px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-teal-500">
                        <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-4 py-2.5 rounded-r-lg text-sm font-semibold transition-colors">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Bottom Bar --}}
        <div class="border-t border-gray-800">
            <div class="container-public py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <span class="text-xs text-gray-500">© {{ date('Y') }} RS Azra Bogor. Hak Cipta Dilindungi.</span>
                <div class="flex items-center gap-6 text-xs text-gray-500">
                    <a href="#" class="hover:text-gray-300 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-gray-300 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
