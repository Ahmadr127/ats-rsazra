<x-layouts.public title="Terima Penawaran - RS Azra" main-class="w-full bg-paper">

<div class="ui-shell w-full py-8">
    <x-ui.card class="mx-auto w-full max-w-2xl text-center">
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f0f7e6] text-[20px] font-bold text-secondary-dark">✓</span>
        <h1 class="ui-title mt-4">Terima Penawaran Kerja</h1>
        <p class="ui-subtitle mx-auto mt-2 max-w-md">Periksa detail penawaran di bawah, lalu unggah surat yang sudah Anda tanda tangani untuk menerima. Tindakan ini tidak dapat dibatalkan.</p>

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
                @if ($offering->catatan)
                    <div class="flex justify-between gap-4">
                        <dt class="text-[13px] text-ink-3">Catatan</dt>
                        <dd class="text-right text-[14px] text-ink">{{ $offering->catatan }}</dd>
                    </div>
                @endif
                @if ($offering->suratUrl())
                    <div class="flex justify-between gap-4">
                        <dt class="text-[13px] text-ink-3">Surat Penawaran</dt>
                        <dd class="text-right text-[14px]">
                            <a href="{{ $offering->suratUrl() }}" target="_blank" rel="noopener" class="font-medium text-primary hover:underline">Lihat / unduh surat</a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        @if ($errors->any())
            <div class="mt-4 rounded-lg bg-red-50 px-4 py-2.5 text-left text-[12px] text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ request()->getRequestUri() }}" enctype="multipart/form-data" class="mt-6 text-left">
            @csrf
            <label class="mb-1.5 block text-[13px] font-medium text-ink-2">Unggah Surat Penawaran yang Sudah Di-TTD <span class="text-red-500">*</span></label>
            <input type="file" name="surat_ttd" accept=".pdf,application/pdf"
                class="ui-textarea focus-ring file:mr-2 file:border-0 file:bg-primary/10 file:text-[12px] file:font-medium file:text-primary file:px-2 file:py-1 file:rounded">
            <p class="mt-1 text-[11px] text-ink-4">PDF, maksimal 3 MB. Wajib diunggah untuk menerima penawaran.</p>
            <x-ui.button type="submit" class="mt-4 w-full">Kirim Surat & Terima Penawaran</x-ui.button>
        </form>
    </x-ui.card>
</div>

</x-layouts.public>
