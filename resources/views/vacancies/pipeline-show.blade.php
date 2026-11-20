<x-layouts.app title="{{ $application->candidate->nama_lengkap }} - Pipeline - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $application->candidate->nama_lengkap }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('lowongan.pipeline.show.export-pdf', [$lowongan, $application]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors ease-out duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export PDF
                </a>
            @if ($currentStage)
                @php
                    $stageBadgeClass = match ($currentStage->status->value) {
                        'aktif' => 'bg-blue-100 text-blue-700',
                        'reserved' => 'bg-amber-100 text-amber-700',
                        'selesai' => 'bg-green-100 text-green-700',
                        'gagal' => 'bg-red-100 text-red-600',
                        default => 'bg-gray-100 text-gray-500',
                    };
                    $stageStatusLabel = match ($currentStage->status->value) {
                        'aktif' => 'Aktif',
                        'reserved' => 'Ditangguhkan',
                        'selesai' => 'Selesai',
                        'gagal' => 'Ditolak',
                        default => ucfirst($currentStage->status->value),
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $stageBadgeClass }}">
                    {{ $stageStatusLabel }}
                </span>
            @endif
            </div>{{-- /flex items-center --}}
        </div>{{-- /flex items-start justify-between --}}
    </div>{{-- /mb-5 --}}

    {{-- Stage progress strip (click a step to see its decision detail) --}}
    <div x-data="stageDetailModal()">
    @if ($snapshotStages->isNotEmpty())
        <div class="mb-5 overflow-x-auto">
            <div class="flex items-start min-w-max">
                @foreach ($snapshotStages as $index => $stage)
                    @php
                        $appStage = $application->stages->firstWhere('key', $stage->key);
                        $isCurrent = $currentStage && $currentStage->key === $stage->key;
                        $stageStatus = $appStage?->status?->value ?? 'pending';

                        $dotClass = match (true) {
                            $isCurrent && $stageStatus === 'aktif' => 'bg-blue-500 ring-2 ring-blue-200',
                            $isCurrent && $stageStatus === 'reserved' => 'bg-amber-500 ring-2 ring-amber-200',
                            $isCurrent && $stageStatus === 'gagal' => 'bg-red-500 ring-2 ring-red-200',
                            $stageStatus === 'selesai' => 'bg-green-500',
                            $stageStatus === 'gagal' => 'bg-red-400',
                            $stageStatus === 'reserved' => 'bg-amber-400',
                            $stageStatus === 'aktif' => 'bg-blue-400',
                            default => 'bg-gray-200',
                        };

                        $labelClass = $isCurrent ? 'font-semibold text-gray-900' : 'text-gray-400';
                        $lineClass = $stageStatus === 'selesai' ? 'bg-green-300' : 'bg-gray-200';
                    @endphp

                    @if ($index > 0)
                        <div class="w-8 h-0.5 flex-shrink-0 mt-1.5 {{ $lineClass }}"></div>
                    @endif
                    <button
                        type="button"
                        data-stage-id="{{ $appStage?->id }}"
                        data-stage-nama="{{ $stage->nama }}"
                        @click="open($event.currentTarget.dataset)"
                        title="Lihat detail {{ $stage->nama }}"
                        class="flex flex-col items-center group cursor-pointer rounded-lg px-1 py-0.5 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary/40 transition-colors"
                    >
                        <div class="w-3 h-3 rounded-full flex-shrink-0 transition-transform group-hover:scale-125 {{ $dotClass }}"></div>
                        <span class="text-[10px] mt-1 px-1 {{ $labelClass }} whitespace-nowrap group-hover:underline underline-offset-2">{{ $stage->nama }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- General stage-detail modal (content loaded via AJAX) --}}
    <div
        x-show="openState"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-label="Detail tahap pipeline"
        @keydown.window.escape="close()"
    >
        <div class="absolute inset-0 bg-black/40" @click="close()"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-100 px-5 py-3 flex items-center justify-between gap-3 rounded-t-xl">
                <h2 class="text-sm font-semibold text-gray-900 truncate" x-text="title">Detail Tahap</h2>
                <button type="button" @click="close()" class="p-1.5 rounded-md text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors cursor-pointer" aria-label="Tutup">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="px-5 py-4">
                <template x-if="loading">
                    <p class="text-xs text-gray-400 py-6 text-center">Memuat detail tahap…</p>
                </template>
                <template x-if="!loading && error">
                    <p class="text-xs text-red-500 py-6 text-center" x-text="error"></p>
                </template>
                <div x-show="!loading && !error" x-html="html"></div>
            </div>
        </div>
    </div>

    <script>
        function stageDetailModal() {
            return {
                openState: false,
                loading: false,
                error: null,
                html: '',
                title: 'Detail Tahap',

                async open(dataset) {
                    const stageId = dataset.stageId;
                    this.title = dataset.stageNama || 'Detail Tahap';
                    this.html = '';
                    this.error = null;
                    this.openState = true;
                    document.body.style.overflow = 'hidden';

                    if (!stageId) {
                        this.error = 'Tahap ini belum diproses.';
                        return;
                    }

                    this.loading = true;
                    try {
                        const url = @js(route('lowongan.pipeline.tahap', [$lowongan, $application, '__STAGE_ID__'])).replace('__STAGE_ID__', stageId);
                        const res = await fetch(url, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        });
                        if (!res.ok) {
                            throw new Error(res.status === 403 ? 'Anda tidak berhak melihat detail tahap ini.' : 'Gagal memuat detail tahap.');
                        }
                        this.html = await res.text();
                    } catch (e) {
                        this.error = e.message || 'Gagal memuat detail tahap.';
                    } finally {
                        this.loading = false;
                    }
                },

                close() {
                    this.openState = false;
                    document.body.style.overflow = '';
                },
            };
        }
    </script>
    </div>{{-- /x-data stageDetailModal --}}

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

        {{-- Left: Candidate info (2/5 width) --}}
        <div class="lg:col-span-2 space-y-4">
            @include('vacancies.partials._informasi-kandidat', ['application' => $application, 'lowongan' => $lowongan])
        </div>

        {{-- Right: Stage action panel (3/5 width) --}}
        <div class="lg:col-span-3 space-y-4">

            @if (!$currentStage)
                <div class="bg-white rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                    Tidak ada tahap aktif untuk kandidat ini.
                </div>
            @elseif (!$isUserPic && $picLabel)
                <div class="bg-white rounded-xl border border-gray-100 p-8 flex flex-col items-center justify-center text-center min-h-48 gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-800 mb-1">Bukan Penanggung Jawab Tahap Ini</p>
                        <p class="text-xs text-gray-500">Tahap <span class="font-medium text-gray-700">{{ $currentStage->nama }}</span> ditangani oleh <span class="font-medium text-gray-700">{{ $picLabel }}</span>.</p>
                    </div>
                </div>
            @else
                @php $stageKey = $currentStage->key; @endphp

                @if ($stageKey === 'lamaran')
                    @include('vacancies.partials._aksi-lamaran', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif (str_starts_with($stageKey, 'skrining_cv'))
                    @include('vacancies.partials._aksi-skrining', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'tes_kompetensi')
                    @include('vacancies.partials._aksi-tes-kompetensi', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                        'testAllReviewed' => $testAllReviewed,
                    ])
                @elseif ($stageKey === 'tes_disc')
                    @include('vacancies.partials._aksi-tes-disc', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'tes_mbti')
                    @include('vacancies.partials._aksi-tes-mbti', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif (in_array($stageKey, ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur']))
                    @include('vacancies.partials._aksi-wawancara', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                        'assignedTemplates' => $assignedTemplates,
                        'assignedReadinessTemplates' => $assignedReadinessTemplates,
                    ])
                @elseif ($stageKey === 'surat_penawaran')
                    @include('vacancies.partials._aksi-surat-penawaran', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'mcu')
                    @include('vacancies.partials._aksi-mcu', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'onboarding')
                    @include('vacancies.partials._aksi-onboarding', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @else
                    <div class="bg-white rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                        Tidak ada panel aksi untuk tahap ini.
                    </div>
                @endif
            @endif

            @include('vacancies.partials._hasil-wawancara-sebelumnya', [
                'application' => $application,
                'lowongan' => $lowongan,
                'interviewStages' => $interviewStages,
                'interviewTemplatesByStage' => $interviewTemplatesByStage,
            ])

        </div>
    </div>

</x-layouts.app>
