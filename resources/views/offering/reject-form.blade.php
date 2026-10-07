<x-layouts.public title="Tolak Penawaran - RS Azra" main-class="w-full bg-paper">

<div class="ui-shell w-full py-8">
    <x-ui.card class="mx-auto w-full max-w-2xl text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-[20px] font-bold text-amber-700">!</span>
        <h1 class="ui-title mt-4">Tolak Penawaran Kerja</h1>
        <p class="ui-subtitle mx-auto mt-2 max-w-md">Anda akan menolak penawaran kerja dari RS Azra. Tindakan ini tidak dapat dibatalkan.</p>

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

        <form method="POST" action="{{ request()->fullUrl() }}" class="mt-6 text-left">
            @csrf
            <x-ui.textarea label="Alasan Penolakan (opsional)" name="rejection_reason" rows="4" placeholder="Mohon beri tahu alasan Anda menolak penawaran ini...">{{ old('rejection_reason') }}</x-ui.textarea>
            <x-ui.button type="submit" class="mt-4 w-full">Konfirmasi Penolakan</x-ui.button>
        </form>
    </x-ui.card>
</div>

</x-layouts.public>
