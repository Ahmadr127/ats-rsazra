{{-- Read-only detail for a single pipeline stage (loaded into the stage-detail modal) --}}
@php
    $stageStatus = $stage->status;
    $statusBadge = match ($stageStatus->value) {
        'aktif' => 'bg-blue-100 text-blue-700',
        'reserved' => 'bg-amber-100 text-amber-800',
        'selesai' => 'bg-green-100 text-green-700',
        'gagal' => 'bg-red-100 text-red-600',
        default => 'bg-gray-100 text-gray-500',
    };
    $statusLabel = $stageStatus->label();
    // Decision carried by the stage status itself for decision stages.
    $decisionLabel = match ($stageStatus->value) {
        'selesai' => 'Lulus',
        'gagal' => 'Ditolak',
        'reserved' => 'Ditangguhkan',
        'aktif' => 'Belum diputuskan',
        default => 'Belum diproses',
    };
    $fmt = fn ($ts) => $ts?->translatedFormat('d M Y, H:i') ?? '—';
@endphp

<div>
    {{-- Stage header --}}
    <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-3 mb-3">
        <div class="min-w-0">
            <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Tahap {{ $stage->position }}</p>
            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $stage->nama }}</h3>
        </div>
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $statusBadge }} shrink-0">{{ $statusLabel }}</span>
    </div>

    <dl class="divide-y divide-gray-100">

        {{-- ═══ LAMARAN ═══ --}}
        @if ($stage->key === 'lamaran')
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Tanggal Melamar</dt><dd class="text-xs text-gray-800">{{ $fmt($application->created_at) }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Posisi</dt><dd class="text-xs text-gray-800">{{ $lowongan->judul_posisi }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Unit</dt><dd class="text-xs text-gray-800">{{ $lowongan->unit->nama }}</dd></div>
            <div class="py-2 text-[11px] text-gray-400">Tahap ini otomatis selesai ketika lamaran terkirim.</div>
        @endif

        {{-- ═══ SKRINING CV (HR & USER) ═══ --}}
        @if (in_array($stage->key, ['skrining_cv_hr', 'skrining_cv_user'], true))
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Keputusan</dt><dd class="text-xs font-medium text-gray-800">{{ $decisionLabel }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $stage->catatan ?: '—' }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Diputuskan Oleh</dt><dd class="text-xs text-gray-800">{{ $stage->reviewedBy?->name ?? '—' }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Tanggal</dt><dd class="text-xs text-gray-800">{{ $fmt($stage->updated_at) }}</dd></div>
        @endif

        {{-- ═══ TES KOMPETENSI ═══ --}}
        @if ($stage->key === 'tes_kompetensi')
            @php $submission = $application->testSubmission; @endphp
            @if (! $submission)
                <div class="py-3 text-xs text-gray-400">Tautan tes belum dibuat (tahap belum dilanjutkan ke sini).</div>
            @else
                @php
                    $tesStatus = ! $submission->started_at
                        ? 'Tautan terkirim, kandidat belum mulai'
                        : ($submission->submitted_at ? 'Sudah dikirim' : 'Sedang dikerjakan');
                @endphp
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Status Tes</dt><dd class="text-xs text-gray-800">{{ $tesStatus }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Mulai</dt><dd class="text-xs text-gray-800">{{ $fmt($submission->started_at) }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Dikirim</dt><dd class="text-xs text-gray-800">{{ $fmt($submission->submitted_at) }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Total Skor</dt><dd class="text-xs text-gray-800">{{ $submission->submitted_at ? ($submission->total_skor ?? 'Belum dihitung') : '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Keputusan</dt><dd class="text-xs font-medium text-gray-800">{{ $decisionLabel }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $stage->catatan ?: '—' }}</dd></div>
                @if ($submission->submitted_at && $submission->answers->isNotEmpty())
                    <div class="py-2">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1.5">Jawaban Kandidat</p>
                        <ol class="space-y-2">
                            @foreach ($submission->answers as $answer)
                                <li class="border border-gray-100 rounded-lg p-2.5 bg-gray-50/50">
                                    <p class="text-[11px] text-gray-700 leading-snug mb-1">{{ $answer->question?->pertanyaan ?? '(pertanyaan dihapus)' }}</p>
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-[11px] text-gray-600 min-w-0">{{ $answer->selectedOption?->teks_opsi ?: ($answer->jawaban_teks ?: '—') }}</p>
                                        <span class="text-[10px] font-medium text-gray-500 shrink-0 tabular-nums">
                                            {{ $answer->is_reviewed ? ($answer->skor.' poin') : 'belum dinilai' }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            @endif
        @endif

        {{-- ═══ WAWANCARA (USER / MANAJER HR / DIREKTUR) ═══ --}}
        @if (in_array($stage->key, ['wawancara_user', 'wawancara_manajer_hr', 'wawancara_direktur'], true))
            @php $result = $stage->interviewResult; @endphp
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Jadwal</dt><dd class="text-xs text-gray-800">{{ $fmt($stage->jadwal) }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Lokasi</dt><dd class="text-xs text-gray-800">{{ $stage->lokasi ?: '—' }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Pewawancara</dt><dd class="text-xs text-gray-800">{{ $stage->interviewer?->name ?? '—' }}</dd></div>

            @if ($result)
                @php
                    $keputusanLabel = match ($result->keputusan) {
                        'lulus' => 'Lulus',
                        'gagal' => 'Ditolak',
                        'reserved' => 'Ditangguhkan',
                        default => ucfirst($result->keputusan),
                    };
                @endphp
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Keputusan</dt><dd class="text-xs font-medium text-gray-800">{{ $keputusanLabel }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $result->catatan ?: '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Diisi Oleh</dt><dd class="text-xs text-gray-800">{{ $result->interviewer?->name ?? '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Tanggal</dt><dd class="text-xs text-gray-800">{{ $fmt($result->submitted_at ?? $result->updated_at) }}</dd></div>

                @if ($result->ratings->isNotEmpty())
                    <div class="py-2">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1.5">Penilaian</p>
                        @foreach ($result->ratings->groupBy('interview_template_id') as $group)
                            <div class="mb-2">
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $group->first()->interviewTemplate?->nama ?? 'Template dihapus' }}</p>
                                <div class="space-y-1">
                                    @foreach ($group as $rating)
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-gray-600">{{ $rating->nama_kriteria }}</span>
                                            <span class="font-medium text-gray-800 tabular-nums">{{ $rating->nilai }}/5</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if ($result->readinessAnswers->isNotEmpty())
                    <div class="py-2">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1.5">Pertanyaan Kesiapan</p>
                        @foreach ($result->readinessAnswers->groupBy('interview_template_id') as $group)
                            <div class="mb-2">
                                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $group->first()->interviewTemplate?->nama ?? 'Template dihapus' }}</p>
                                <div class="space-y-1">
                                    @foreach ($group as $answer)
                                        <div class="flex items-center justify-between gap-2 text-[11px]">
                                            <span class="text-gray-600">{{ $answer->pertanyaan }}</span>
                                            <span class="font-medium {{ $answer->jawaban ? 'text-green-600' : 'text-red-500' }}">{{ $answer->jawaban ? 'Ya' : 'Tidak' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="py-3 text-xs text-gray-400">Belum ada hasil wawancara direkam.</div>
            @endif
        @endif

        {{-- ═══ TES DiSC / MBTI (EKSTERNAL) ═══ --}}
        @if (in_array($stage->key, ['tes_disc', 'tes_mbti'], true))
            @php
                $isDisc = $stage->key === 'tes_disc';
                $testLabel = $isDisc ? 'DISC' : 'MBTI';
                $submission = $isDisc ? $application->discSubmission : $application->mbtiSubmission;
                $proofs = $submission?->proof_paths ?? [];
            @endphp
            @if (! $submission)
                <div class="py-3 text-xs text-gray-400">Submission tes {{ $testLabel }} belum dibuat.</div>
            @else
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Link Tes</dt><dd class="text-xs text-gray-800 min-w-0 break-all">
                    @if ($submission->link_tes)
                        <a href="{{ $submission->link_tes }}" target="_blank" rel="noopener noreferrer" class="text-primary hover:text-primary-dark underline underline-offset-2">{{ $submission->link_tes }}</a>
                    @else
                        <span class="text-amber-600">Belum dikirim</span>
                    @endif
                </dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800 whitespace-pre-line">{{ $submission->catatan ?: '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Email Dikirim</dt><dd class="text-xs text-gray-800">{{ $fmt($submission->sent_at) }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Bukti Tes</dt><dd class="text-xs text-gray-800">
                    @if ($submission->proof_uploaded_at)
                        Diterima {{ $fmt($submission->proof_uploaded_at) }}
                    @else
                        <span class="text-amber-600">Belum diunggah</span>
                    @endif
                </dd></div>
                @if ($proofs !== [])
                    <div class="py-2">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1.5">Gambar Bukti</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach ($proofs as $path)
                                <a href="{{ Storage::url($path) }}" target="_blank" rel="noopener noreferrer" class="block overflow-hidden rounded-lg border border-gray-200">
                                    <img src="{{ Storage::url($path) }}" alt="Bukti tes {{ $testLabel }} {{ $loop->iteration }}" class="h-20 w-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        @endif

        {{-- ═══ SURAT PENAWARAN ═══ --}}
        @if ($stage->key === 'surat_penawaran')
            @php $offer = $application->offeringLetter; @endphp
            @if (! $offer)
                <div class="py-3 text-xs text-gray-400">Belum ada surat penawaran dikirim.</div>
            @else
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Posisi Ditawarkan</dt><dd class="text-xs text-gray-800">{{ $offer->jabatan_ditawarkan }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Gaji</dt><dd class="text-xs text-gray-800">Rp {{ number_format((float) $offer->gaji, 0, ',', '.') }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Mulai Kerja</dt><dd class="text-xs text-gray-800">{{ $offer->tanggal_mulai?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Dikirim</dt><dd class="text-xs text-gray-800">{{ $fmt($offer->sent_at) }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Status</dt><dd class="text-xs font-medium text-gray-800">{{ $offer->status->label() }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Direspon</dt><dd class="text-xs text-gray-800">{{ $fmt($offer->responded_at) }}</dd></div>
                @if ($offer->rejection_reason)
                    <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Alasan Ditolak</dt><dd class="text-xs text-gray-800">{{ $offer->rejection_reason }}</dd></div>
                @endif
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $offer->catatan ?: '—' }}</dd></div>
            @endif
        @endif

        {{-- ═══ MCU ═══ --}}
        @if ($stage->key === 'mcu')
            @php $mcu = $application->mcuResult; @endphp
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Jadwal</dt><dd class="text-xs text-gray-800">{{ $fmt($stage->jadwal) }}</dd></div>
            <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Lokasi</dt><dd class="text-xs text-gray-800">{{ $stage->lokasi ?: '—' }}</dd></div>
            @if (! $mcu)
                <div class="py-3 text-xs text-gray-400">Hasil MCU belum direkam.</div>
            @else
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Keputusan</dt><dd class="text-xs font-medium text-gray-800">{{ $mcu->keputusan->label() }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $mcu->catatan ?: '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Diputuskan Oleh</dt><dd class="text-xs text-gray-800">{{ $mcu->reviewer?->name ?? '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Tanggal</dt><dd class="text-xs text-gray-800">{{ $fmt($mcu->submitted_at) }}</dd></div>
                @if ($mcu->dokumen_path)
                    <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Dokumen</dt><dd class="text-xs"><a href="{{ Storage::url($mcu->dokumen_path) }}" target="_blank" rel="noopener" class="text-primary hover:text-primary-dark underline underline-offset-2">Lihat dokumen MCU</a></dd></div>
                @endif
            @endif
        @endif

        {{-- ═══ ONBOARDING ═══ --}}
        @if ($stage->key === 'onboarding')
            @php $onboarding = $application->onboardingResult; @endphp
            @if (! $onboarding)
                <div class="py-3 text-xs text-gray-400">Undangan onboarding belum dikirim.</div>
            @else
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Tanggal Bergabung</dt><dd class="text-xs text-gray-800">{{ $onboarding->tanggal_bergabung?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Undangan Dikirim</dt><dd class="text-xs text-gray-800">{{ $fmt($onboarding->sent_at) }}</dd></div>
                <div class="flex gap-3 py-2"><dt class="w-32 shrink-0 text-[10px] font-medium text-gray-400 uppercase tracking-wide pt-0.5">Catatan</dt><dd class="text-xs text-gray-800">{{ $onboarding->catatan ?: '—' }}</dd></div>
            @endif
        @endif

    </dl>

    {{-- Footer: last update audit --}}
    <div class="border-t border-gray-100 mt-3 pt-2.5 flex items-center justify-between gap-2 text-[10px] text-gray-400">
        <span>Terakhir diperbarui {{ $fmt($stage->updated_at) }}</span>
        @if ($stage->reviewedBy || $stage->interviewer)
            <span>Oleh {{ $stage->reviewedBy?->name ?? $stage->interviewer?->name }}</span>
        @endif
    </div>
</div>
