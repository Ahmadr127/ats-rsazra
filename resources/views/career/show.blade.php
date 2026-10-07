<x-layouts.public title="{{ $vacancy->judul_posisi }} - RS Azra" main-class="w-full bg-paper">

<div class="container-public py-14">
    <a href="{{ route('karier.index') }}" class="btn btn-ghost btn-sm inline-flex items-center gap-2 mb-10">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/></svg>
        Kembali ke Lowongan
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-16 items-start">
        {{-- Left: job detail --}}
        <div>
            <div class="flex items-center gap-2.5 text-brand-teal text-xs font-bold uppercase tracking-wider mb-4">
                <span class="w-7 h-px bg-brand-teal"></span>
                {{ $vacancy->unit->nama }}
            </div>
            <h1 class="font-jakarta text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">{{ $vacancy->judul_posisi }}</h1>
            <div class="flex flex-wrap gap-1.5 mt-6 mb-8">
                <span class="badge badge-primary">{{ $vacancy->unit->nama }}</span>
                <span class="badge badge-outline">{{ $vacancy->jenis_pekerjaan->label() }}</span>
                @if ($vacancy->created_at->gte(now()->subDays(3)))
                    <span class="badge badge-success">Baru</span>
                @endif
                @if ($vacancy->tenggat_lamaran->lte(now()->addDays(7)))
                    <span class="badge badge-danger">Mendesak</span>
                @endif
            </div>

            <div class="mb-9">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-t-2 border-slate-900 pt-3.5 mb-3">Deskripsi Pekerjaan</h2>
                <div class="text-base leading-relaxed text-slate-700 whitespace-pre-line">{{ $vacancy->deskripsi_pekerjaan }}</div>
            </div>

            <div class="mb-9">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-900 border-t-2 border-slate-900 pt-3.5 mb-3">Kualifikasi</h2>
                <div class="text-base leading-relaxed text-slate-700 whitespace-pre-line">{{ $vacancy->kualifikasi }}</div>
            </div>
        </div>

        {{-- Right: sidebar --}}
        <aside class="lg:sticky lg:top-24">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 bg-primary text-white text-xs font-bold uppercase tracking-wider">Detail Posisi</div>
                <div class="p-5 flex flex-col gap-4">
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Jenis Pekerjaan</div>
                        <div class="text-[15px] font-medium text-slate-900">{{ $vacancy->jenis_pekerjaan->label() }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Jumlah Posisi</div>
                        <div class="text-[15px] font-medium text-slate-900">{{ $vacancy->jumlah_posisi }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Tenggat Lamaran</div>
                        <div class="text-[15px] font-medium text-slate-900">{{ $vacancy->tenggat_lamaran->format('d M Y') }}</div>
                    </div>
                    <div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Ditayangkan</div>
                        <div class="text-[15px] font-medium text-slate-900">{{ $vacancy->created_at->locale('id')->diffForHumans() }}</div>
                    </div>
                </div>
                <a href="{{ route('karier.lamar', $vacancy) }}" class="btn btn-primary mx-5 mb-5 justify-center">
                    Lamar Sekarang
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>
                </a>
            </div>
        </aside>
    </div>
</div>

</x-layouts.public>
