<x-layouts.public title="Karir RS Azra - Bergabung dalam Karya Penyembuhan yang Bermakna" main-class="w-full">
    <!-- BEGIN: HeroSection -->
    <section class="w-full bg-slate-50 border-b border-slate-200" data-purpose="career-hero-section">
        <div class="container-public py-8">
                <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl bg-slate-900 group h-80" id="hero-carousel">
                    @forelse ($slides as $slide)
                        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out {{ $loop->first ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none' }}">
                            <img alt="Flyer lowongan {{ $slide->judul_posisi }} di {{ $slide->unit->nama }}"
                                class="w-full h-full object-cover object-center scale-100 transform transition-transform duration-[7000ms] ease-out"
                                src="{{ $slide->flyerUrl() }}" />
                        </div>
                    @empty
                        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out opacity-100 pointer-events-auto" style="background: linear-gradient(120deg, #005F53 0%, #0F172A 100%);">
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
                    class="relative z-10 max-w-4xl mx-auto mt-6 card p-3 flex flex-col md:flex-row gap-3">
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
                    <button type="submit" class="btn btn-primary whitespace-nowrap">
                        Cari Posisi
                    </button>
                </form>
            </div>
        </section>
        <!-- END: HeroSection -->
        <!-- BEGIN: FeaturedJobsSection -->
    </section>
    <!-- END: HeroSection -->

    <!-- BEGIN: FeaturedJobsSection -->
    <section class="w-full bg-brand-bgsoft border-b border-slate-200" data-purpose="featured-job-listings" id="lowongan">
            <div class="container-public">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-brand-teal text-xs font-bold uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="radio" aria-hidden="true" class="lucide lucide-radio w-3.5 h-3.5 text-emerald-500 animate-pulse"><path d="M16.247 7.761a6 6 0 0 1 0 8.478"></path><path d="M19.075 4.933a10 10 0 0 1 0 14.134"></path><path d="M4.925 19.067a10 10 0 0 1 0-14.134"></path><path d="M7.753 16.239a6 6 0 0 1 0-8.478"></path><circle cx="12" cy="12" r="2"></circle></svg>
                            Kebutuhan Mendesak &amp; Terbuka
                        </div>
                        <h2 class="font-jakarta text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 tracking-tight">
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
                    <div class="card p-12 text-center">
                        <h3 class="font-jakarta text-2xl font-extrabold text-slate-900 tracking-tight">Tidak ada lowongan yang cocok.</h3>
                        <p class="text-sm text-slate-500 mt-2">Coba kata kunci atau filter yang berbeda.</p>
                        <a href="{{ route('karier.index') }}#lowongan"
                            class="btn btn-primary mt-6 inline-flex items-center gap-2">
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
                            <article class="card card-interactive overflow-hidden flex flex-col justify-between group">
                                <a href="{{ route('karier.show', $vacancy) }}" class="block relative bg-slate-100" aria-label="Lihat lowongan {{ $vacancy->judul_posisi }} — {{ $vacancy->unit->nama }}">
                                    <img src="{{ $vacancy->flyerUrl() }}" alt="Flyer lowongan {{ $vacancy->judul_posisi }} di {{ $vacancy->unit->nama }}"
                                        loading="lazy" class="w-full h-44 object-cover object-top" />
                                    @if ($isNew || $isUrgent)
                                        <div class="absolute top-3 left-3 flex gap-1.5">
                                            @if ($isNew)
                                                <span class="badge badge-success">Baru</span>
                                            @endif
                                            @if ($isUrgent)
                                                <span class="badge badge-danger">Mendesak</span>
                                            @endif
                                        </div>
                                    @endif
                                </a>
                                <div class="p-6 flex flex-col flex-1">
                                    <div class="flex items-center justify-between mb-4">
                                        <span class="badge badge-primary">{{ $vacancy->unit->nama }}</span>
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
                                        <a class="btn btn-ghost btn-sm inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-all"
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
        </div>
    </section>
    <!-- END: FeaturedJobsSection -->

    <!-- BEGIN: BantuanBanner -->
    <section class="w-full bg-brand-bgsoft border-b border-slate-200">
        <div class="container-public py-8">
                <div class="card p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
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
                        class="btn btn-primary shrink-0">
                        Hubungi HR RS Azra
                    </a>
                </div>
            </div>
        </section>
        <!-- END: BantuanBanner -->
        <!-- BEGIN: RecruitmentProcessSection -->
        <section class="section bg-white" data-purpose="hiring-process" id="proses">
            <div class="container-public">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-brand-teal text-xs font-bold uppercase tracking-wider">Transparan &amp; Terukur</span>
                    <h2 class="font-jakarta text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1 tracking-tight">Tahapan Seleksi Rekrutmen</h2>
                    <p class="text-sm text-slate-500 mt-2">Seluruh proses seleksi dilakukan secara objektif dengan orientasi kompetensi klinis serta etika pelayanan.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                    <div class="card bg-brand-bgsoft">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">1</span>
                        <h3 class="text-base font-bold text-slate-900">Registrasi &amp; Seleksi Berkas</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Pengisian portal kandidat, verifikasi keabsahan STR, ijazah terakreditasi, sertifikasi kompetensi medis.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 3 - 5 Hari Kerja</span>
                    </div>
                    <div class="card bg-brand-bgsoft">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">2</span>
                        <h3 class="text-base font-bold text-slate-900">Asesmen Kompetensi &amp; Kredensial</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Tes tertulis kompetensi bidang, psikotes komprehensif, dan verifikasi komite medik / komite keperawatan.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 2 - 4 Hari Kerja</span>
                    </div>
                    <div class="card bg-brand-bgsoft">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">3</span>
                        <h3 class="text-base font-bold text-slate-900">Wawancara User &amp; Direksi</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Sesi tatap muka dengan Kepala Instalasi, Direktur Medis, dan Tim HR terkait studi kasus dan keselarasan kultur.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Estimasi: 1 Minggu</span>
                    </div>
                    <div class="card bg-brand-bgsoft">
                        <span class="w-8 h-8 rounded-full bg-brand-teal text-white font-bold text-xs flex items-center justify-center mb-4">4</span>
                        <h3 class="text-base font-bold text-slate-900">Medical Check-Up (MCU) &amp; Onboarding</h3>
                        <p class="text-xs text-slate-600 mt-2 leading-relaxed">Pemeriksaan kesehatan kerja menyeluruh, penandatanganan kontrak kerja, dan orientasi tata laksana RS.</p>
                        <span class="inline-block mt-4 text-[11px] font-semibold text-brand-teal">Selamat Bergabung!</span>
                    </div>
                </div>
            </div>
        </section>
        <!-- END: RecruitmentProcessSection -->

    @push('scripts')
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
    @endpush
</x-layouts.public>
