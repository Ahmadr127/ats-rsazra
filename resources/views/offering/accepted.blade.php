<x-layouts.public title="Penawaran Diterima - RS Azra" main-class="w-full bg-paper">

<div class="ui-shell w-full py-8">
    <x-ui.card class="mx-auto w-full max-w-2xl text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f0f7e6] text-[20px] font-bold text-secondary-dark">✓</span>
        <h1 class="ui-title mt-4">Penawaran Diterima</h1>
        <p class="ui-subtitle mx-auto mt-2 max-w-md">Terima kasih telah menerima penawaran kerja dari RS Azra. Tim HR kami akan segera menghubungi Anda.</p>

        <div class="mt-6 rounded-xl bg-paper p-5 text-left">
            <h2 class="text-[14px] font-semibold text-ink">Ringkasan Penawaran</h2>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-4">
                    <dt class="text-[13px] text-ink-3">Posisi</dt>
                    <dd class="text-right text-[14px] font-medium text-ink">{{ $offering->jabatan_ditawarkan }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[13px] text-ink-3">Gaji</dt>
                    <dd class="text-right text-[14px] font-medium text-ink">{{ $offering->gaji }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-[13px] text-ink-3">Tanggal Mulai</dt>
                    <dd class="text-right text-[14px] font-medium text-ink">{{ $offering->tanggal_mulai->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>
    </x-ui.card>
</div>

</x-layouts.public>
