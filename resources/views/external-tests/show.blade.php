@php
    $application = $submission->application;
    $vacancy = $application->vacancy;
    $proofs = $submission->proof_paths ?? [];
    $isCompleted = $stage->status === \App\Enums\ApplicationStageStatus::Selesai;
    $isFailed = $stage->status === \App\Enums\ApplicationStageStatus::Gagal;
@endphp

<x-layouts.test
    title="Tes {{ $testLabel }} - {{ $vacancy->judul_posisi }}"
    heading="Tes {{ $testLabel }}"
    position="{{ $vacancy->judul_posisi }}"
    meta="Tes Eksternal"
>
    <div class="mx-auto w-full max-w-2xl space-y-4">

        @if (session('success'))
            <x-ui.alert tone="success" title="Berhasil">{{ session('success') }}</x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert tone="danger" title="Periksa kembali">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        @if ($isFailed)
            <x-ui.card class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-[20px] font-bold text-red-500">×</span>
                <h1 class="ui-section-title mt-4">Tahap Tes Tidak Diloloskan</h1>
                <p class="ui-help mx-auto mt-2 max-w-md">Terima kasih atas waktu Anda. Berdasarkan hasil proses rekrutmen, Anda tidak dapat kami lanjutkan pada tahap ini.</p>
                <p class="mt-6 text-[12px] text-ink-4">Halaman ini dapat ditutup.</p>
            </x-ui.card>
        @elseif ($isCompleted)
            <x-ui.card class="text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#f0f7e6] text-[20px] font-bold text-secondary-dark">✓</span>
                <h1 class="ui-section-title mt-4">Bukti Tes {{ $testLabel }} Sudah Diterima</h1>
                <p class="ui-help mx-auto mt-2 max-w-md">Terima kasih. Bukti tes Anda telah kami terima dan proses seleksi dilanjutkan otomatis.</p>
                @if ($submission->proof_uploaded_at)
                    <div class="mt-6 rounded-xl bg-paper p-4 text-left">
                        <div class="flex justify-between gap-4 py-1 text-[14px]">
                            <span class="text-ink-3">Posisi</span>
                            <span class="text-right font-medium text-ink">{{ $vacancy->judul_posisi }}</span>
                        </div>
                        <div class="flex justify-between gap-4 py-1 text-[14px]">
                            <span class="text-ink-3">Waktu Pengiriman</span>
                            <span class="font-medium text-ink">{{ $submission->proof_uploaded_at->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>
                @endif
                <p class="mt-6 text-[12px] text-ink-4">Halaman ini dapat ditutup.</p>
            </x-ui.card>
        @elseif (! $submission->link_tes)
            <x-ui.alert tone="warning" title="Link tes belum tersedia">Link tes eksternal belum dikirim oleh tim HR. Silakan hubungi tim HR RS Azra untuk informasi lebih lanjut.</x-ui.alert>
        @else
            <x-ui.card>
                <h1 class="ui-section-title">Tes {{ $testLabel }} Eksternal</h1>
                <p class="ui-help mt-2">Kerjakan tes melalui tautan di bawah ini. Setelah selesai, unggah screenshot atau foto bukti hasil tes pada formulir di halaman ini.</p>

                <div class="mt-4 rounded-xl border border-line-2 bg-paper p-4">
                    <p class="text-[12px] font-medium text-ink-3">Tautan tes</p>
                    <a href="{{ $submission->link_tes }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-[14px] font-medium text-primary underline underline-offset-2">{{ $submission->link_tes }}</a>
                    <x-ui.button href="{{ $submission->link_tes }}" class="mt-3 w-full sm:w-auto" target="_blank" rel="noopener noreferrer">Buka Tes Eksternal</x-ui.button>
                </div>

                @if ($submission->catatan)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-[12px] font-semibold text-ink">Catatan dari HR</p>
                        <p class="mt-1 whitespace-pre-line text-[13px] text-ink-2">{{ $submission->catatan }}</p>
                    </div>
                @endif
            </x-ui.card>

            @if (! empty($proofs))
                <x-ui.card>
                    <h2 class="text-[14px] font-semibold text-ink">Bukti yang Sudah Diunggah</h2>
                    <p class="ui-help mt-1">Tahap belum selesai. Anda dapat mengunggah ulang bukti jika diperlukan.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                        @foreach ($proofs as $path)
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer" class="overflow-hidden rounded-lg border border-line-2">
                                <img src="{{ Storage::url($path) }}" alt="Bukti tes {{ $testLabel }} {{ $loop->iteration }}" class="h-28 w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

            <x-ui.card>
                <h2 class="text-[14px] font-semibold text-ink">Unggah Bukti Tes</h2>
                <p class="ui-help mt-1">Unggah 1–5 gambar bukti tes (JPG, JPEG, PNG, atau WEBP). Maksimal 5 MB per gambar. Setelah bukti dikirim, tahap pipeline otomatis dilanjutkan.</p>

                <form method="POST" action="{{ route($submitRoute, $submission->token) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label for="proofs" class="mb-1 block text-[13px] font-medium text-ink-2">Gambar bukti</label>
                        <input
                            id="proofs"
                            type="file"
                            name="proofs[]"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            multiple
                            required
                            class="block w-full rounded-lg border border-line-2 bg-white px-3 py-2 text-[13px] text-ink-2 file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-3 file:py-1.5 file:text-[12px] file:font-semibold file:text-white hover:file:bg-primary-dark"
                        >
                        @error('proofs')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                        @error('proofs.*')
                            <p class="mt-1 text-[12px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="submit" class="w-full sm:w-auto">Kirim Bukti & Lanjut</x-ui.button>
                </form>
            </x-ui.card>

            <p class="px-1 text-center text-[11px] text-ink-4">Setelah bukti berhasil dikirim, halaman ini tidak dapat digunakan lagi untuk mengunggah bukti tambahan.</p>
        @endif
    </div>
</x-layouts.test>
