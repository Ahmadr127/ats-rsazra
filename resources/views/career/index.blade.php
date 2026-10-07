<!doctype html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Karir RS Azra - Bergabung dalam Karya Penyembuhan yang Bermakna</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-tab.png') }}">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,500;1,600&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-brand-dark font-jakarta antialiased selection:bg-brand-teal selection:text-white min-h-screen flex flex-col">
    <!-- BEGIN: MainNavbar -->
    <header class="w-full sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 transition-all">
        <nav class="w-full px-6 lg:px-12 xl:px-16 h-20 flex items-center justify-between">
            <div class="flex items-center gap-10">
                <a class="flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-brand-teal rounded-lg p-1" href="{{ route('karier.index') }}">
                    <div class="relative flex items-center justify-center">
                        <span class="text-3xl font-extrabold tracking-tight text-brand-dark font-jakarta lowercase">azra</span>
                        <div class="ml-1 relative flex items-center justify-center w-8 h-8">
                            <span class="absolute w-7 h-2.5 bg-brand-teal rounded-full transition-transform group-hover:scale-105"></span>
                            <span class="absolute h-7 w-2.5 bg-[#8AC249] rounded-full transition-transform group-hover:scale-105"></span>
                        </div>
                    </div>
                    <div class="hidden sm:flex flex-col border-l border-slate-300 pl-3">
                        <span class="text-xs font-bold tracking-widest uppercase text-brand-teal">CAREER HUB</span>
                        <span class="text-[11px] text-slate-500 font-medium">Karier Medis &amp; Non-Medis</span>
                    </div>
                </a>
                <div class="hidden xl:flex items-center gap-7 text-[14.5px] font-semibold text-slate-700">
                    <a class="hover:text-brand-teal transition-colors" href="#lowongan">Lowongan Kerja</a>
                    <a class="hover:text-brand-teal transition-colors" href="#proses">FAQ</a>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 hover:text-brand-teal transition-colors" href="{{ route('login') }}">Masuk Pelamar</a>
                <a class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-brand-teal rounded-lg hover:bg-brand-darkteal shadow-sm transition-colors" href="#lowongan">Kirim CV</a>
            </div>
        </nav>
    </header>
    <!-- END: MainNavbar -->
    <!-- BEGIN: HeroSection -->
    <main class="flex-grow w-full">
        <section class="w-full bg-slate-50 pt-6 pb-16 px-6 lg:px-12 xl:px-16 border-b border-slate-200" data-purpose="career-hero-section">
            <div class="w-full max-w-[1600px] mx-auto">
                <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl bg-slate-900 group h-80" id="hero-carousel">
                    @forelse ($slides as $slide)
                        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $loop->first ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none' }}">
                            <img alt="Flyer lowongan {{ $slide->judul_posisi }} di {{ $slide->unit->nama }}"
                                class="w-full h-full object-cover object-center scale-100 transform transition-transform duration-[7000ms] ease-out"
                                src="{{ $slide->flyerUrl() }}" />
                            <div class="absolute inset-0" style="background: linear-gradient(to top, rgba(2,12,10,0.88) 0%, rgba(2,12,10,0.35) 55%, transparent 85%);"></div>
                            <div class="absolute inset-x-0 bottom-0 p-6 sm:p-10 z-20">
                                <p class="font-playfair italic text-emerald-300 text-sm mb-1">{{ $slide->unit->nama }}</p>
                                <div class="flex items-end justify-between gap-4 flex-wrap">
                                    <h2 class="font-playfair text-white font-semibold text-2xl sm:text-4xl leading-tight max-w-2xl">{{ $slide->judul_posisi }}</h2>
                                    <a href="{{ route('karier.show', $slide) }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-brand-dark text-sm font-bold rounded-lg hover:bg-brand-teal hover:text-white transition-colors">
                                        Lamar Sekarang
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right w-4 h-4"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100 pointer-events-auto" style="background: linear-gradient(120deg, #005F53 0%, #0F172A 100%);">
                            <div class="absolute inset-0 flex flex-col justify-center p-6 sm:p-10 z-20">
                                <p class="font-playfair italic text-emerald-300 text-sm mb-1">RS Azra Bogor</p>
                                <h2 class="font-playfair text-white font-semibold text-2xl sm:text-4xl leading-tight max-w-2xl">Bergabung dalam karya penyembuhan yang bermakna.</h2>
                            </div>
                        </div>
                    @endforelse
                    <button aria-label="Slide sebelumnya"
                        class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 z-30 p-2.5 sm:p-3 rounded-full bg-black/30 hover:bg-black/60 text-white backdrop-blur-md border border-white/20 opacity-80 group-hover:opacity-100 transition-all focus:outline-none focus:ring-2 focus:ring-brand-teal"
                        id="carousel-prev">
                        <svg class="w-5 h-5" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="m15 18-6-6 6-6"></path></svg>
                    </button>
                    <button aria-label="Slide selanjutnya"
                        class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 z-30 p-2.5 sm:p-3 rounded-full bg-black/30 hover:bg-black/60 text-white backdrop-blur-md border border-white/20 opacity-80 group-hover:opacity-100 transition-all focus:outline-none focus:ring-2 focus:ring-brand-teal"
                        id="carousel-next">
                        <svg class="w-5 h-5" fill="none" height="24" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="m9 18 6-6-6-6"></path></svg>
                    </button>
                    <div class="absolute bottom-5 sm:bottom-6 right-6 sm:right-10 z-30 flex items-center gap-2.5" id="carousel-dots">
                        @foreach ($slides as $slide)
                            <button aria-label="Ke slide {{ $loop->iteration }}" data-index="{{ $loop->index }}"
                                class="carousel-dot h-2.5 rounded-full transition-all duration-300 {{ $loop->first ? 'w-8 bg-brand-teal' : 'w-2.5 bg-white/50 hover:bg-white/80' }}"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Search terintegrasi (fungsionalitas portal: q + unit) --}}
                <form method="GET" action="{{ route('karier.index') }}#lowongan"
                    class="relative z-10 max-w-4xl mx-auto -mt-0 mt-6 bg-white rounded-2xl shadow-xl border border-slate-200 p-3 flex flex-col md:flex-row gap-3">
                    <div class="flex items-center gap-3 flex-1 px-4 py-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="search" aria-hidden="true" class="lucide lucide-search w-5 h-5 text-slate-400 shrink-0"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Posisi atau kata kunci — mis. perawat ICU…"
                            class="w-full outline-none bg-transparent text-[15px] text-slate-900 placeholder:text-slate-400" />
                    </div>
                    <div class="flex items-center gap-3 md:w-64 px-4 py-2 md:border-l border-slate-200">
                        <select name="unit[]" onchange="this.form.submit()" class="w-full outline-none bg-transparent text-[15px] text-slate-900 cursor-pointer">
                            <option value="">Semua Unit</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}" {{ in_array($unit->id, $unitFilter ?? []) ? 'selected' : '' }}>{{ $unit->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach ((array) request('type', []) as $t)
                        <input type="hidden" name="type[]" value="{{ $t }}" />
                    @endforeach
                    <button type="submit" class="px-8 py-3.5 bg-brand-teal text-white text-sm font-semibold rounded-xl hover:bg-brand-darkteal transition-colors whitespace-nowrap">
                        Cari Posisi
                    </button>
                </form>
            </div>
        </section>
        <!-- END: HeroSection -->
        <!-- BEGIN: FeaturedJobsSection -->
        <section class="w-full py-16 px-6 lg:px-12 xl:px-16 bg-brand-bgsoft border-b border-slate-200" data-purpose="featured-job-listings" id="lowongan">
            <div class="w-full max-w-[1600px] mx-auto">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-brand-teal text-xs font-bold uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="radio" aria-hidden="true" class="lucide lucide-radio w-3.5 h-3.5 text-emerald-500 animate-pulse"><path d="M16.247 7.761a6 6 0 0 1 0 8.478"></path><path d="M19.075 4.933a10 10 0 0 1 0 14.134"></path><path d="M4.925 19.067a10 10 0 0 1 0-14.134"></path><path d="M7.753 16.239a6 6 0 0 1 0-8.478"></path><circle cx="12" cy="12" r="2"></circle></svg>
                            Kebutuhan Mendesak &amp; Terbuka
                        </div>
                        <h2 class="font-playfair text-2xl sm:text-3xl font-semibold text-slate-900 mt-1">
                            {{ $vacancies->total() }} Lowongan Karir Terpilih
                        </h2>
                        <p class="text-sm text-slate-500 mt-1">
                            Daftar secara daring dengan verifikasi berkas terstandarisasi.
                        </p>
                    </div>
                </div>

                @php
                    $baseParams = array_filter(['q' => request('q'), 'type' => request('type')]);
                @endphp
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <span class="text-xs text-slate-500 font-medium">Filter Unit:</span>
                    <div class="inline-flex rounded-lg p-1 bg-white border border-slate-200 text-xs font-semibold flex-wrap">
                        <a href="{{ route('karier.index', $baseParams) }}#lowongan"
                            class="px-3 py-1.5 rounded-md {{ empty($unitFilter) ? 'bg-brand-teal text-white' : 'text-slate-600 hover:text-brand-teal' }}">
                            Semua ({{ $totalRoles }})
                        </a>
                        @foreach ($units as $unit)
                            @php
                                $active = in_array($unit->id, $unitFilter ?? []);
                                $ids = $active ? array_values(array_diff($unitFilter, [$unit->id])) : array_merge($unitFilter ?? [], [$unit->id]);
                                $params = $baseParams;
                                if (! empty($ids)) { $params['unit'] = $ids; }
                            @endphp
                            <a href="{{ route('karier.index', $params) }}#lowongan"
                                class="px-3 py-1.5 rounded-md {{ $active ? 'bg-brand-teal text-white' : 'text-slate-600 hover:text-brand-teal' }}">
                                {{ $unit->nama }} ({{ $unit->published_count }})
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap mb-10">
                    <span class="text-xs text-slate-500 font-medium">Jenis:</span>
                    <div class="inline-flex rounded-lg p-1 bg-white border border-slate-200 text-xs font-semibold flex-wrap">
                        @php $typeBase = array_filter(['q' => request('q'), 'unit' => request('unit')]); @endphp
                        <a href="{{ route('karier.index', $typeBase) }}#lowongan"
                            class="px-3 py-1.5 rounded-md {{ empty($typeFilter) ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-brand-teal' }}">
                            Semua Jenis
                        </a>
                        @foreach ($employmentTypes as $type)
                            @php
                                $tActive = in_array($type->value, $typeFilter ?? []);
                                $tIds = $tActive ? array_values(array_diff($typeFilter, [$type->value])) : array_merge($typeFilter ?? [], [$type->value]);
                                $tParams = $typeBase;
                                if (! empty($tIds)) { $tParams['type'] = $tIds; }
                            @endphp
                            <a href="{{ route('karier.index', $tParams) }}#lowongan"
                                class="px-3 py-1.5 rounded-md {{ $tActive ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-brand-teal' }}">
                                {{ $type->label() }} ({{ $typeCounts[$type->value] ?? 0 }})
                            </a>
                        @endforeach
                    </div>
                    @if (request()->hasAny(['q', 'unit', 'type']))
                        <a href="{{ route('karier.index') }}#lowongan" class="text-xs text-brand-teal underline underline-offset-4 font-medium">Reset semua filter</a>
                    @endif
                </div>

                @if ($vacancies->isEmpty())
                    <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                        <h3 class="font-playfair text-2xl text-slate-900">Tidak ada lowongan yang cocok.</h3>
                        <p class="text-sm text-slate-500 mt-2">Coba kata kunci atau filter yang berbeda.</p>
                        <a href="{{ route('karier.index') }}#lowongan"
                            class="inline-flex items-center gap-2 mt-6 px-6 py-3 bg-brand-teal text-white text-sm font-semibold rounded-lg hover:bg-brand-darkteal transition-colors">
                            Lihat semua lowongan
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        @foreach ($vacancies as $vacancy)
                            @php
                                $isNew = $vacancy->created_at->gte(now()->subDays(3));
                                $isUrgent = $vacancy->tenggat_lamaran->lte(now()->addDays(7));
                            @endphp
                            <article class="bg-white rounded-xl border border-slate-200 overflow-hidden flex flex-col justify-between hover:shadow-lg hover:border-brand-teal/50 transition-all duration-200 group">
                                <a href="{{ route('karier.show', $vacancy) }}" class="block relative bg-slate-100" aria-label="Lihat lowongan {{ $vacancy->judul_posisi }} — {{ $vacancy->unit->nama }}">
                                    <img src="{{ $vacancy->flyerUrl() }}" alt="Flyer lowongan {{ $vacancy->judul_posisi }} di {{ $vacancy->unit->nama }}"
                                        loading="lazy" class="w-full h-44 object-cover object-top" />
                                    @if ($isNew || $isUrgent)
                                        <div class="absolute top-3 left-3 flex gap-1.5">
                                            @if ($isNew)
                                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-brand-teal">Baru</span>
                                            @endif
                                            @if ($isUrgent)
                                                <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-red-100 text-red-700">Mendesak</span>
                                            @endif
                                        </div>
                                    @endif
                                </a>
                                <div class="p-6 flex flex-col flex-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-100 text-brand-teal">{{ $vacancy->unit->nama }}</span>
                                        <span class="text-xs text-slate-400 font-medium">{{ $vacancy->jenis_pekerjaan->label() }}</span>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-teal transition-colors leading-snug">
                                        <a href="{{ route('karier.show', $vacancy) }}">{{ $vacancy->judul_posisi }}</a>
                                    </h3>
                                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="map-pin" aria-hidden="true" class="lucide lucide-map-pin w-3.5 h-3.5 text-slate-400"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        {{ $vacancy->unit->nama }}, Bogor
                                    </p>
                                    <ul class="mt-4 space-y-2 text-xs text-slate-600 border-t border-slate-100 pt-3">
                                        <li class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-3.5 h-3.5 text-brand-teal"><path d="M20 6 9 17l-5-5"></path></svg>
                                            {{ $vacancy->jumlah_posisi }} posisi tersedia
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-3.5 h-3.5 text-brand-teal"><path d="M20 6 9 17l-5-5"></path></svg>
                                            {{ $vacancy->jenis_pekerjaan->label() }}
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="check" aria-hidden="true" class="lucide lucide-check w-3.5 h-3.5 text-brand-teal"><path d="M20 6 9 17l-5-5"></path></svg>
                                            Batas {{ $vacancy->tenggat_lamaran->format('d M Y') }}
                                        </li>
                                    </ul>
                                    <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between mt-auto">
                                        <span class="text-[11px] text-slate-400">{{ $vacancy->created_at->locale('id')->diffForHumans() }}</span>
                                        <a class="text-xs font-bold text-brand-teal hover:text-brand-darkteal group-hover:translate-x-0.5 transition-all inline-flex items-center gap-1"
                                            href="{{ route('karier.show', $vacancy) }}">
                                            Rincian &amp; Lamar
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-right" aria-hidden="true" class="lucide lucide-arrow-right w-3.5 h-3.5"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    <div class="mt-10 flex items-center justify-between gap-4 flex-wrap">
                        <p class="text-xs text-slate-500">
                            Menampilkan <strong class="text-slate-900">{{ $vacancies->firstItem() }}–{{ $vacancies->lastItem() }}</strong>
                            dari <strong class="text-slate-900">{{ $vacancies->total() }}</strong> posisi
                        </p>
                        <div>{{ $vacancies->links() }}</div>
                    </div>
                @endif
            </div>
        </section>
        <!-- END: FeaturedJobsSection -->
        <!-- BEGIN: BantuanBanner -->
        <section class="w-full px-6 lg:px-12 xl:px-16 bg-brand-bgsoft border-b border-slate-200">
            <div class="w-full max-w-[1600px] mx-auto py-8">
                <div class="bg-white rounded-xl p-5 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="p-2.5 bg-emerald-50 text-brand-teal rounded-lg shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="file-text" aria-hidden="true" class="lucide lucide-file-text w-5 h-5"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"></path><path d="M14 2v5a1 1 0 0 0 1 1h5"></path><path d="M10 9H8"></path><path d="M16 13H8"></path><path d="M16 17H8"></path></svg>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Tidak menemukan posisi yang sesuai bidang Anda?</p>
                            <p class="text-xs text-slate-500">Pantau halaman ini berkala — lowongan baru terbit setiap periode rekrutmen.</p>
                        </div>
                    </div>
                    <a href="https://wa.me/6281219801997" target="_blank" rel="noopener"
                        class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shrink-0 transition-colors">
                        Hubungi HR RS Azra
                    </a>
                </div>
            </div>
        </section>
        <!-- END: BantuanBanner -->
        <!-- BEGIN: RecruitmentProcessSection -->
        <section class="w-full py-16 px-6 lg:px-12 xl:px-16 bg-white" data-purpose="hiring-process" id="proses">
            <div class="w-full max-w-[1600px] mx-auto">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-brand-teal text-xs font-bold uppercase tracking-wider">Transparan &amp; Terukur</span>
                    <h2 class="font-playfair text-2xl sm:text-3xl font-semibold text-slate-900 mt-1">Tahapan Seleksi Rekrutmen</h2>
                    <p class="text-sm text-slate-500 mt-2">Seluruh proses seleksi dilakukan secara objektif dengan orientasi kompetensi klinis serta etika pelayanan.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                    <div class="bg-brand-bgsoft rounded-xl p-6 border border-slate-200 relative">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">1</span>
                        <h3 class="text-base font-bold text-slate-900">Registrasi &amp; Seleksi Berkas</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Pengisian portal kandidat, verifikasi keabsahan STR, ijazah terakreditasi, sertifikasi kompetensi medis.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 3 - 5 Hari Kerja</span>
                    </div>
                    <div class="bg-brand-bgsoft rounded-xl p-6 border border-slate-200 relative">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">2</span>
                        <h3 class="text-base font-bold text-slate-900">Asesmen Kompetensi &amp; Kredensial</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Tes tertulis kompetensi bidang, psikotes komprehensif, dan verifikasi komite medik / komite keperawatan.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 2 - 4 Hari Kerja</span>
                    </div>
                    <div class="bg-brand-bgsoft rounded-xl p-6 border border-slate-200 relative">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">3</span>
                        <h3 class="text-base font-bold text-slate-900">Wawancara User &amp; Direksi</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Sesi tatap muka dengan Kepala Instalasi, Direktur Medis, dan Tim HR terkait studi kasus dan keselarasan kultur.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 1 Minggu</span>
                    </div>
                    <div class="bg-brand-bgsoft rounded-xl p-6 border border-slate-200 relative">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">4</span>
                        <h3 class="text-base font-bold text-slate-900">Medical Check-Up (MCU) &amp; Onboarding</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Pemeriksaan kesehatan kerja menyeluruh, penandatanganan kontrak kerja, dan orientasi tata laksana RS.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Selamat Bergabung!</span>
                    </div>
                </div>
            </div>
        </section>
        <!-- END: RecruitmentProcessSection -->
    </main>
    <!-- BEGIN: MainFooter -->
    <footer class="w-full bg-slate-950 text-slate-300 pt-14 pb-8 px-6 lg:px-12 xl:px-16 border-t border-slate-800" data-purpose="career-portal-footer">
        <div class="w-full max-w-[1600px] mx-auto">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 pb-8 border-b border-slate-800">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl font-bold text-white lowercase tracking-tight font-jakarta">azra</span>
                        <div class="relative flex items-center justify-center w-6 h-6">
                            <span class="absolute w-5 h-2 bg-emerald-500 rounded-full"></span>
                            <span class="absolute h-5 w-2 bg-[#8AC249] rounded-full"></span>
                        </div>
                        <span class="text-xs font-semibold tracking-wider uppercase text-emerald-400 pl-2 border-l border-slate-700">Karir &amp; Rekrutmen</span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-md leading-relaxed">Melayani kesehatan masyarakat sejak 1994 dengan profesionalisme, empati, dan standar pelayanan medis paripurna.</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 sm:gap-8 text-xs text-slate-400">
                    <div><span class="text-slate-500 block mb-0.5">Alamat</span>Jl. Raya Pajajaran No.219, Bogor</div>
                    <div><span class="text-slate-500 block mb-0.5">Kontak HR</span>rekrutmen@rsazra.co.id • (0251) 8318456</div>
                </div>
            </div>
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© {{ date('Y') }} RS Azra Bogor. Hak Cipta Dilindungi.</p>
                <div class="flex items-center gap-6">
                    <a class="hover:text-slate-300 transition-colors" href="#">Kebijakan Privasi</a>
                    <a class="hover:text-slate-300 transition-colors" href="#">Syarat &amp; Ketentuan</a>
                    <a class="hover:text-slate-300 transition-colors" href="https://rsazra.co.id">rsazra.co.id</a>
                </div>
            </div>
        </div>
    </footer>
    <!-- END: MainFooter -->
    <!-- BEGIN: Scripts -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script data-purpose="hero-carousel-and-lucide-initializer">
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) { lucide.createIcons(); }
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.carousel-dot');
            const prevBtn = document.getElementById('carousel-prev');
            const nextBtn = document.getElementById('carousel-next');
            const carouselContainer = document.getElementById('hero-carousel');
            if (!slides.length) return;
            let currentIndex = 0;
            let slideInterval = null;
            function updateSlide(index) {
                slides.forEach((slide, idx) => {
                    const on = idx === index;
                    slide.classList.toggle('opacity-100', on);
                    slide.classList.toggle('pointer-events-auto', on);
                    slide.classList.toggle('opacity-0', !on);
                    slide.classList.toggle('pointer-events-none', !on);
                });
                dots.forEach((dot, idx) => {
                    dot.className = 'carousel-dot h-2.5 rounded-full transition-all duration-300 '
                        + (idx === index ? 'w-8 bg-brand-teal' : 'w-2.5 bg-white/50 hover:bg-white/80');
                });
                currentIndex = index;
            }
            function nextSlide() { updateSlide((currentIndex + 1) % slides.length); }
            function prevSlide() { updateSlide((currentIndex - 1 + slides.length) % slides.length); }
            function startAutoSlide() { stopAutoSlide(); slideInterval = setInterval(nextSlide, 4500); }
            function stopAutoSlide() { if (slideInterval) { clearInterval(slideInterval); } }
            if (nextBtn) { nextBtn.addEventListener('click', () => { nextSlide(); startAutoSlide(); }); }
            if (prevBtn) { prevBtn.addEventListener('click', () => { prevSlide(); startAutoSlide(); }); }
            dots.forEach((dot) => { dot.addEventListener('click', (e) => { updateSlide(parseInt(e.currentTarget.getAttribute('data-index'), 10)); startAutoSlide(); }); });
            if (carouselContainer) {
                carouselContainer.addEventListener('mouseenter', stopAutoSlide);
                carouselContainer.addEventListener('mouseleave', startAutoSlide);
            }
            startAutoSlide();
        });
    </script>
    <!-- END: Scripts -->
</body>
</html>
