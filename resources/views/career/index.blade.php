<x-layouts.public
    :title="$seoTitle"
    :metaDescription="$seoDescription"
    :canonical="route('home')"
    :ogImage="asset('images/logo.png')"
    main-class="w-full bg-paper"
>

<form id="career-search" method="GET" action="{{ route('home') }}"></form>

<x-ui.page-hero
    :eyebrow="$heroEyebrow"
    :title="$heroTitle"
    :lede="$heroLede"
    compact
/>

<div class="ui-shell grid w-full items-start gap-6 pb-10 pt-4 lg:grid-cols-[260px_1fr]">
    @php
        $activeFilterCount = count($unitFilter ?? []) + count($typeFilter ?? []) + (request('q') ? 1 : 0);
    @endphp
    <aside class="w-full lg:sticky lg:top-20" x-data="{ filtersOpen: {{ request()->hasAny(['q', 'unit', 'type']) ? 'true' : 'false' }} }">
        <button
            type="button"
            x-on:click="filtersOpen = !filtersOpen"
            class="ui-btn ui-btn-secondary mb-4 w-full lg:hidden"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Saring &amp; Pencarian
            @if ($activeFilterCount > 0)
                <span class="ml-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[12px] font-bold text-white">{{ $activeFilterCount }}</span>
            @endif
            <svg class="ml-auto h-4 w-4 transition-transform" :class="{ 'rotate-180': filtersOpen }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
        </button>

        <div :class="filtersOpen ? 'block' : 'hidden'" class="lg:block">
        <x-ui.card>
            <div class="mb-3">
                <x-ui.input
                    label="Pencarian"
                    id="q-input"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="mis. perawat ICU..."
                    form="career-search"
                />
                <x-ui.button type="submit" form="career-search" class="mt-2 w-full py-2! text-[13px]">Cari</x-ui.button>
            </div>

            <div class="my-4 border-t border-line-2"></div>

            <div class="mb-4 flex items-center justify-between">
                <h2 class="ui-label">Saring</h2>
                @if (request()->hasAny(['q', 'unit', 'type']))
                    <a href="{{ route('home') }}" class="text-[12px] font-semibold text-primary hover:text-primary-dark">Reset</a>
                @endif
            </div>

            <p class="ui-label mb-2">Departemen</p>
            <div class="mb-5 space-y-1">
                @forelse ($units as $unit)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-[13px] text-ink-2 hover:bg-paper">
                        <input
                            form="career-search"
                            type="checkbox"
                            name="unit[]"
                            value="{{ $unit->id }}"
                            {{ in_array($unit->id, $unitFilter ?? []) ? 'checked' : '' }}
                            x-on:change="document.getElementById('career-search').submit()"
                            class="h-4 w-4 rounded border-line text-primary focus:ring-primary/30"
                        >
                        <span class="min-w-0 flex-1 truncate">{{ $unit->nama }}</span>
                        <span class="text-[12px] text-ink-4">{{ $unit->published_count }}</span>
                    </label>
                @empty
                    <p class="text-[13px] text-ink-4">Tidak ada departemen.</p>
                @endforelse
            </div>

            <p class="ui-label mb-2">Jenis Pekerjaan</p>
            <div class="space-y-1">
                @foreach ($employmentTypes as $type)
                    <label class="flex cursor-pointer items-center gap-2.5 rounded-lg px-2 py-1.5 text-[13px] text-ink-2 hover:bg-paper">
                        <input
                            form="career-search"
                            type="checkbox"
                            name="type[]"
                            value="{{ $type->value }}"
                            {{ in_array($type->value, $typeFilter ?? []) ? 'checked' : '' }}
                            x-on:change="document.getElementById('career-search').submit()"
                            class="h-4 w-4 rounded border-line text-primary focus:ring-primary/30"
                        >
                        <span class="min-w-0 flex-1 truncate">{{ $type->label() }}</span>
                        <span class="text-[12px] text-ink-4">{{ $typeCounts[$type->value] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>
        </x-ui.card>
        </div>
    </aside>

    <div class="min-w-0">
        @if ($vacancies->isEmpty())
            <x-ui.card class="mt-5 text-center">
                <h3 class="ui-section-title">Tidak ada lowongan yang cocok.</h3>
                <p class="ui-help mx-auto mt-2 max-w-md">Coba kata kunci atau filter yang berbeda, atau reset filter untuk melihat semua posisi.</p>
                @if (request()->hasAny(['q', 'unit', 'type']))
                    <x-ui.button href="{{ route('home') }}" class="mt-4">Lihat semua lowongan</x-ui.button>
                @endif
            </x-ui.card>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($vacancies as $vacancy)
                    @php
                        $isNew = $vacancy->created_at->gte(now()->subDays(3));
                        $isUrgent = $vacancy->tenggat_lamaran->lte(now()->addDays(7));
                    @endphp
                    <a href="{{ route('karier.show', $vacancy) }}" class="ui-card group overflow-hidden p-0! transition hover:-translate-y-0.5 hover:shadow-md" aria-label="Lihat lowongan {{ $vacancy->judul_posisi }}">
                        <div class="relative aspect-[4/5] w-full overflow-hidden bg-paper-2">
                            @if ($isNew || $isUrgent)
                                <div class="absolute left-3 top-3 z-10 flex gap-1.5">
                                    @if ($isNew)
                                        <x-ui.badge tone="success">Baru</x-ui.badge>
                                    @endif
                                    @if ($isUrgent)
                                        <x-ui.badge tone="warning">Mendesak</x-ui.badge>
                                    @endif
                                </div>
                            @endif
                            <img
                                src="{{ $vacancy->flyerUrl() }}"
                                alt="Flyer lowongan {{ $vacancy->judul_posisi }}"
                                loading="lazy"
                                width="600"
                                height="800"
                                class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                            >
                        </div>
                        <div class="p-3">
                            <p class="truncate text-[12px] font-semibold uppercase tracking-wide text-primary">{{ $vacancy->unit->nama }}</p>
                            <h3 class="mt-1 line-clamp-2 min-h-10 text-[14px] font-semibold leading-snug text-ink">{{ $vacancy->judul_posisi }}</h3>
                            <p class="mt-1 text-[12px] text-ink-3">Tenggat {{ $vacancy->tenggat_lamaran->format('d M Y') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[12px] uppercase tracking-wide text-ink-3">Menampilkan <strong class="text-ink">{{ $vacancies->firstItem() }}–{{ $vacancies->lastItem() }}</strong> dari <strong class="text-ink">{{ $vacancies->total() }}</strong> posisi</p>
                <div>{{ $vacancies->links() }}</div>
            </div>
        @endif
    </div>
</div>

</x-layouts.public>
