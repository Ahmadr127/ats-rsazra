<x-layouts.public title="Lamaran Terkirim - RS Azra" main-class="w-full bg-paper">

<x-ui.page-hero
    eyebrow="Konfirmasi Lamaran"
    title="Lamaran Berhasil Dikirim"
    lede="{{ $application->vacancy->judul_posisi }} — {{ $application->vacancy->unit->nama }}"
/>

<div class="ui-shell w-full py-6">
    <x-ui.alert tone="success" title="Lamaran Anda telah kami terima." class="mb-5">Simpan halaman ini atau catat kode lamaran Anda untuk memantau status.</x-ui.alert>

    <div class="grid w-full items-start gap-5 lg:grid-cols-2">
        <x-ui.card>
            <h2 class="ui-section-title">Ringkasan Lamaran</h2>
            <dl class="mt-4 divide-y divide-line-2">
                <div class="flex gap-4 py-2.5">
                    <dt class="ui-label w-28 shrink-0 pt-0.5">Nama</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ $application->candidate->nama_lengkap }}</dd>
                </div>
                <div class="flex gap-4 py-2.5">
                    <dt class="ui-label w-28 shrink-0 pt-0.5">Email</dt>
                    <dd class="min-w-0 break-all text-[14px] font-medium text-ink">{{ $application->candidate->email }}</dd>
                </div>
                <div class="flex gap-4 py-2.5">
                    <dt class="ui-label w-28 shrink-0 pt-0.5">Posisi</dt>
                    <dd class="text-[14px] font-medium text-ink">{{ $application->vacancy->judul_posisi }}</dd>
                </div>
                <div class="flex gap-4 py-2.5">
                    <dt class="ui-label w-28 shrink-0 pt-0.5">Kode</dt>
                    <dd class="min-w-0 break-all text-[13px] text-ink">{{ $application->token }}</dd>
                </div>
            </dl>
        </x-ui.card>

        <x-ui.card>
            <h2 class="ui-section-title">Tahapan Seleksi</h2>
            <ol class="mt-4 space-y-2.5">
                @foreach ($application->stages as $stage)
                    <li class="flex items-center gap-3 border-b border-line-2 pb-2.5 last:border-0 last:pb-0">
                        @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-secondary text-[12px] font-bold text-white">✓</span>
                        @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary"><span class="h-2 w-2 rounded-full bg-white"></span></span>
                        @else
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-paper-2 text-[12px] font-semibold text-ink-4">{{ $loop->iteration }}</span>
                        @endif
                        <span class="text-[14px] {{ $stage->status === \App\Enums\ApplicationStageStatus::Aktif ? 'font-semibold text-ink' : 'text-ink-3' }}">{{ $stage->nama }}</span>
                    </li>
                @endforeach
            </ol>
        </x-ui.card>
    </div>

    <div class="mt-6 flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('home') }}" class="text-[13px] font-semibold text-primary hover:text-primary-dark">&larr; Lihat lowongan lainnya</a>
        <x-ui.button href="{{ route('karier.lamaran.status', $application->token) }}" class="w-full sm:w-auto">Cek status lamaran &rarr;</x-ui.button>
    </div>
</div>

</x-layouts.public>
