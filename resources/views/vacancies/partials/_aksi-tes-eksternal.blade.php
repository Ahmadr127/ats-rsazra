{{-- Aksi Tahap: Tes Eksternal (DISC/MBTI) --}}
{{-- Variables: $application, $lowongan, $currentStage, $submission, $testLabel, $sendRoute --}}

@php
    $advanceable = $currentStage?->status->isAdvanceable() ?? false;
    $hasProof = $submission?->proof_uploaded_at !== null;
    $proofs = $submission?->proof_paths ?? [];
    $canEdit = $advanceable && ! $hasProof;
    $sentAt = $submission?->sent_at;
@endphp

<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">Tes {{ $testLabel }} Eksternal</h2>
                <p class="text-xs text-gray-500 mt-0.5">Kirim link tes, lalu kandidat mengunggah bukti untuk melanjutkan otomatis.</p>
            </div>
            @if ($advanceable)
                @if ($hasProof)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-700 shrink-0">Bukti diterima</span>
                @elseif ($sentAt)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-700 shrink-0">Link terkirim</span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-100 text-amber-700 shrink-0">Link belum dikirim</span>
                @endif
            @elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Selesai)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-700 shrink-0">Diloloskan</span>
            @elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-600 shrink-0">Ditolak</span>
            @endif
        </div>

        @if ($canEdit)
            <form method="POST" action="{{ route($sendRoute, [$lowongan, $application]) }}" class="space-y-3">
                @csrf

                <div>
                    <label for="link_tes-{{ $testLabel }}" class="block text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Link Tes Eksternal</label>
                    <input
                        id="link_tes-{{ $testLabel }}"
                        type="url"
                        name="link_tes"
                        value="{{ old('link_tes', $submission?->link_tes) }}"
                        required
                        maxlength="2048"
                        placeholder="https://..."
                        class="w-full px-3 py-2 text-xs border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/30 {{ $errors->has('link_tes') ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' }}"
                    >
                    @error('link_tes')
                        <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="catatan-{{ $testLabel }}" class="block text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Catatan untuk Kandidat <span class="normal-case text-gray-400">(opsional)</span></label>
                    <textarea
                        id="catatan-{{ $testLabel }}"
                        name="catatan"
                        rows="3"
                        maxlength="2000"
                        placeholder="Catatan ini akan tampil di email kandidat."
                        class="w-full px-3 py-2 text-xs border border-gray-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-primary/30"
                    >{{ old('catatan', $submission?->catatan) }}</textarea>
                    @error('catatan')
                        <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                @error('email')
                    <p class="text-[11px] text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-primary rounded-lg hover:bg-primary-dark transition-colors cursor-pointer">
                    {{ $sentAt ? 'Simpan & Kirim Ulang' : 'Simpan & Kirim Email' }}
                </button>
                <p class="text-[10px] text-gray-400">Kandidat akan menerima email berisi link tes dan tautan halaman upload bukti.</p>
            </form>
        @endif

        @if ($hasProof)
            <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-[11px] text-green-700">
                Kandidat sudah mengunggah bukti pada {{ $submission->proof_uploaded_at->translatedFormat('d M Y, H:i') }}. Pipeline otomatis dilanjutkan.
            </div>
        @elseif ($advanceable && ! $sentAt)
            <p class="mt-2 text-[11px] text-amber-600">Kandidat belum dapat mengerjakan tes sampai link dikirim.</p>
        @endif

        @if ($advanceable)
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between gap-3">
                <p class="text-[11px] text-gray-400">Tidak ada tombol Lanjutkan manual. Tahap berlanjut otomatis setelah bukti diunggah.</p>
                @permission('application.fail')
                    <form method="POST" action="{{ route('lowongan.lamaran.gagal', [$lowongan, $application]) }}"
                          onsubmit="return confirm('Tolak kandidat ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors cursor-pointer">
                            Tolak
                        </button>
                    </form>
                @endpermission
            </div>
        @endif
    </div>

    @if ($submission)
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Informasi Tes</h3>
            <dl class="divide-y divide-gray-100">
                <div class="flex gap-3 py-2">
                    <dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Link Tes</dt>
                    <dd class="text-xs text-gray-800 min-w-0 break-all">
                        @if ($submission->link_tes)
                            <a href="{{ $submission->link_tes }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:text-primary-dark underline underline-offset-2">{{ $submission->link_tes }}</a>
                        @else
                            <span class="text-gray-400">Belum dikirim</span>
                        @endif
                    </dd>
                </div>
                <div class="flex gap-3 py-2">
                    <dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt>
                    <dd class="text-xs text-gray-800 whitespace-pre-line">{{ $submission->catatan ?: '—' }}</dd>
                </div>
                <div class="flex gap-3 py-2">
                    <dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Dikirim</dt>
                    <dd class="text-xs text-gray-800">{{ $sentAt?->translatedFormat('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div class="flex gap-3 py-2">
                    <dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Status Bukti</dt>
                    <dd class="text-xs text-gray-800">
                        @if ($hasProof)
                            Diterima {{ $submission->proof_uploaded_at->translatedFormat('d M Y, H:i') }}
                        @else
                            <span class="text-amber-600">Belum diunggah</span>
                        @endif
                    </dd>
                </div>
            </dl>

            @if ($proofs !== [])
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-2">Gambar Bukti</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach ($proofs as $path)
                            <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-lg border border-gray-200">
                                <img src="{{ Storage::url($path) }}" alt="Bukti tes {{ $testLabel }} {{ $loop->iteration }}" class="h-20 w-full object-cover">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
