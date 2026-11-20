{{-- Aksi Tahap: Wawancara --}}
{{-- Variables: $application, $lowongan, $currentStage, $assignedTemplates, $assignedReadinessTemplates, $eligibleInterviewers --}}

@if ($errors->any())
    <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

@php
    $authUser = auth()->user();
    $existingResult = $currentStage?->activeInterviewResult;
    $hasSchedule = (bool) $currentStage?->jadwal;
    $isWawancaraUser = $currentStage?->key === \App\Support\InterviewStageMap::USER;

    // Mirror InterviewController::decide(): only the role mapped for this stage
    // (and, for wawancara_user, the assigned interviewer) may submit a decision.
    $deciderKey = \App\Support\InterviewStageMap::stageKeyForDecider($authUser);
    $canDecide = $existingResult === null
        && $deciderKey === $currentStage?->key
        && (! $isWawancaraUser || ($currentStage?->interviewer_id !== null && $currentStage->interviewer_id === $authUser->id));

    // Mirror InterviewController::update(): stage permission + assigned
    // interviewer + the edit window (next stage not filled yet).
    $editPermission = $currentStage
        ? \App\Support\InterviewStageMap::decidePermissionForStage($currentStage->key)
        : null;
    $canEditExisting = $existingResult !== null
        && $currentStage->isInterviewEditable()
        && $editPermission
        && $authUser->hasPermission($editPermission)
        && (! $isWawancaraUser || ($currentStage->interviewer_id !== null && $currentStage->interviewer_id === $authUser->id));
@endphp

{{-- Phase 1: No schedule yet — only schedulers (interview.schedule) may fill this form --}}
@if ($currentStage?->status->isAdvanceable() && ! $hasSchedule)
    @if ($authUser->hasPermission(\App\Support\Permissions::INTERVIEW_SCHEDULE))
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-4">Jadwalkan Wawancara</h2>
        <form action="{{ route('lowongan.wawancara.jadwal', [$lowongan, $application]) }}" method="POST">
            @csrf
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tanggal & Waktu <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="jadwal" required
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                    @error('jadwal')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Lokasi <span class="text-red-500">*</span></label>
                    <input type="text" name="lokasi" required placeholder="Ruang Meeting Lt. 3 / Link Google Meet"
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                    @error('lokasi')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @if ($isWawancaraUser)
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Pewawancara <span class="text-red-500">*</span></label>
                        <select name="interviewer_id" required
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40">
                            <option value="">Pilih pewawancara...</option>
                            @foreach ($eligibleInterviewers as $interviewer)
                                <option value="{{ $interviewer->id }}" @if (old('interviewer_id') == $interviewer->id) selected @endif>
                                    {{ $interviewer->name }} ({{ $interviewer->role?->label ?? 'Tanpa Peran' }})
                                </option>
                            @endforeach
                        </select>
                        @error('interviewer_id')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4">
                <p class="text-xs text-amber-700">Keputusan wawancara baru dapat diberikan setelah jadwal ditetapkan dan form penilaian diisi.</p>
            </div>
            <button type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                Simpan &amp; Kirim Undangan
            </button>
        </form>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-100 p-8 flex flex-col items-center justify-center text-center min-h-48 gap-4">
        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-gray-800 mb-1">Menunggu Jadwal Wawancara</p>
            <p class="text-xs text-gray-500">Jadwal wawancara tahap <span class="font-medium text-gray-700">{{ $currentStage?->nama }}</span> belum ditetapkan. HR / Manajer HR akan menjadwalkannya dan kandidat akan menerima undangan.</p>
        </div>
    </div>
    @endif

{{-- Phase 2: Schedule set, no result yet — show schedule summary + assessment form --}}
@elseif ($currentStage?->status->isAdvanceable() && $hasSchedule && ! $existingResult)
    @include('vacancies.partials._jadwal-wawancara', [
        'application' => $application,
        'lowongan' => $lowongan,
        'currentStage' => $currentStage,
        'eligibleInterviewers' => $eligibleInterviewers,
        'allowReschedule' => true,
    ])

    @include('vacancies.partials._form-penilaian-wawancara', [
        'application' => $application,
        'lowongan' => $lowongan,
        'stage' => $currentStage,
        'assignedTemplates' => $assignedTemplates,
        'assignedReadinessTemplates' => $assignedReadinessTemplates,
        'result' => null,
        'canEdit' => $canDecide,
    ])

{{-- Result already recorded: stay editable until the next stage is filled --}}
@elseif ($existingResult)
    @include('vacancies.partials._jadwal-wawancara', [
        'application' => $application,
        'lowongan' => $lowongan,
        'currentStage' => $currentStage,
        'eligibleInterviewers' => $eligibleInterviewers,
    ])

    @include('vacancies.partials._form-penilaian-wawancara', [
        'application' => $application,
        'lowongan' => $lowongan,
        'stage' => $currentStage,
        'assignedTemplates' => $assignedTemplates,
        'assignedReadinessTemplates' => $assignedReadinessTemplates,
        'result' => $existingResult,
        'canEdit' => $canEditExisting,
        'subheading' => $canEditExisting ? 'Dapat diperbarui sampai tahap berikutnya terisi.' : null,
    ])

@elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-600">Ditolak</span>
    </div>
@endif
