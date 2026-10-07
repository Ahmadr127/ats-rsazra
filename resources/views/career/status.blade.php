<x-layouts.public title="Status Lamaran - RS Azra" main-class="w-full bg-paper">

<x-ui.page-hero
    eyebrow="Status Lamaran"
    title="{{ $application->vacancy->judul_posisi }}"
    lede="{{ $application->vacancy->unit->nama }}"
/>

<div class="ui-shell grid w-full items-start gap-5 py-6 lg:grid-cols-2">
    <x-ui.card>
        <h2 class="ui-section-title">Informasi Pelamar</h2>
        <dl class="mt-4 divide-y divide-line-2">
            <div class="flex gap-4 py-2.5">
                <dt class="ui-label w-28 shrink-0 pt-0.5">Nama</dt>
                <dd class="text-[14px] font-medium text-ink">{{ $application->candidate->nama_lengkap }}</dd>
            </div>
            <div class="flex gap-4 py-2.5">
                <dt class="ui-label w-28 shrink-0 pt-0.5">Posisi</dt>
                <dd class="text-[14px] font-medium text-ink">{{ $application->vacancy->judul_posisi }}</dd>
            </div>
            <div class="flex gap-4 py-2.5">
                <dt class="ui-label w-28 shrink-0 pt-0.5">Unit</dt>
                <dd class="text-[14px] font-medium text-ink">{{ $application->vacancy->unit->nama }}</dd>
            </div>
        </dl>
    </x-ui.card>

    <x-ui.card>
        <h2 class="ui-section-title">Tahapan Seleksi</h2>
        @php
            $gagalStage = $application->stages->firstWhere('status', \App\Enums\ApplicationStageStatus::Gagal);
            $gagalPosition = $gagalStage?->position;
        @endphp
        <ol class="mt-4">
            @foreach ($application->stages as $stage)
                @php
                    $isAfterGagal = $gagalPosition !== null && $stage->position > $gagalPosition;
                @endphp
                <li class="flex gap-3 {{ $isAfterGagal ? 'opacity-40' : '' }}">
                    <div class="flex w-7 shrink-0 flex-col items-center">
                        @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-secondary text-[12px] font-bold text-white">✓</span>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-primary"><span class="h-2 w-2 rounded-full bg-white"></span></span>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-500 text-[12px] font-bold text-white">…</span>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-[12px] font-bold text-white">×</span>
                        @else
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-paper-2 text-[12px] font-semibold text-ink-4">{{ $loop->iteration }}</span>
                        @endif
                        @unless ($loop->last)
                            <span class="my-1 w-px flex-1 bg-line-2"></span>
                        @endunless
                    </div>
                    <div class="min-w-0 pb-5">
                        <p class="text-[14px] font-medium text-ink">{{ $stage->nama }}</p>
                        <p class="mt-0.5 text-[12px] uppercase tracking-wide text-ink-4">
                            @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                                Selesai · {{ $stage->updated_at->format('d/m/Y') }}
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                                Sedang berlangsung
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                                Ditangguhkan
                            @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                                Tidak lolos
                            @else
                                Menunggu
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </x-ui.card>
</div>

<div class="ui-shell w-full pb-8">
    <div class="border-t border-line pt-5">
        <a href="{{ route('home') }}" class="text-[13px] font-semibold text-primary hover:text-primary-dark">&larr; Lihat lowongan lainnya</a>
    </div>
</div>

</x-layouts.public>
