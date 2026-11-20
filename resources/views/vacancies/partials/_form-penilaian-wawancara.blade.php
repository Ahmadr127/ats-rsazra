{{-- Form penilaian wawancara (isi baru & edit hasil yang sudah ada). --}}
{{-- Variables: $application, $lowongan, $stage, $assignedTemplates, $assignedReadinessTemplates, $result (nullable), $canEdit, $heading (optional), $subheading (optional). --}}

@php
    $isEdit = $result !== null;
    $heading = $heading ?? 'Penilaian Wawancara';

    // Only the form that was just submitted should consume old input; several
    // interview forms may be visible on the same page.
    $oldBelongsToThisForm = (string) old('stage_id') === (string) $stage->id;

    $decisionLocked = $isEdit && ! $stage->canChangeInterviewDecision();

    $selectedKeputusan = $decisionLocked
        ? $result->keputusan
        : ($oldBelongsToThisForm ? old('keputusan', $result?->keputusan) : $result?->keputusan);
    $selectedCatatan = $oldBelongsToThisForm ? old('catatan', $result?->catatan) : $result?->catatan;

    $decisionMeta = match ($result?->keputusan) {
        'lulus' => ['bg-green-100 text-green-700', 'Lulus'],
        'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
        'reserved' => $stage->status === \App\Enums\ApplicationStageStatus::Reserved
            ? ['bg-amber-100 text-amber-700', 'Ditangguhkan']
            : ['bg-blue-100 text-blue-700', 'Dilanjutkan'],
        default => ['bg-gray-100 text-gray-500', '-'],
    };

    $savedRatings = [];
    $savedReadiness = [];

    if ($isEdit) {
        foreach ($result->ratings as $savedRating) {
            $savedRatings[$savedRating->interview_template_id.'|'.$savedRating->nama_kriteria] = $savedRating->nilai;
        }

        foreach ($result->readinessAnswers as $savedAnswer) {
            $savedReadiness[$savedAnswer->interview_template_id.'|'.$savedAnswer->pertanyaan] = $savedAnswer->jawaban ? '1' : '0';
        }
    }
@endphp

<div class="bg-white rounded-xl border border-gray-100 {{ $isEdit ? 'overflow-hidden' : 'p-5' }}" @if ($isEdit) x-data="{ open: false }" @endif>
    @if ($isEdit)
        {{-- Accordion header (hanya mode edit) --}}
        <button
            type="button"
            @click="open = !open"
            class="w-full flex items-center justify-between gap-3 p-5 text-left hover:bg-gray-50/60 transition-colors ease-out duration-150"
            aria-expanded="false"
            :aria-expanded="open.toString()"
        >
            <div class="flex items-center gap-3 min-w-0">
                <h2 class="text-sm font-semibold text-gray-800">{{ $heading }}</h2>
                @if (! empty($subheading))
                    <p class="text-[11px] text-gray-500 truncate">{{ $subheading }}</p>
                @endif
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $decisionMeta[0] }}">
                    {{ $decisionMeta[1] }}
                </span>
            </div>
            <svg
                class="w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                :class="{ 'rotate-180': open }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Accordion body (hanya mode edit) --}}
        <div
            x-show="open"
            x-collapse
            class="px-5 pb-5"
        >
    @else
        <div class="flex items-start justify-between gap-3 mb-4">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">{{ $heading }}</h2>
                @if (! empty($subheading))
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $subheading }}</p>
                @endif
            </div>
        </div>
    @endif

    @if ($assignedTemplates->isEmpty() && (! $isEdit || $canEdit))
        <div class="text-center py-4">
            <p class="text-sm font-medium text-gray-700">Belum ada kriteria penilaian</p>
            <p class="text-xs text-gray-500 mt-1 mb-3">
                Tahap {{ $stage->nama }} membutuhkan template Kriteria Penilaian yang dipasang khusus untuk lowongan ini.
            </p>
            @permission('vacancy.interview-templates')
                <a
                    href="{{ route('lowongan.template-wawancara.show', $lowongan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                >
                    Atur Template Wawancara
                </a>
            @else
                <p class="text-xs text-gray-400">Hubungi HR Admin untuk memasang template.</p>
            @endpermission
        </div>
    @elseif (! $canEdit)
        @if (! $isEdit)
            <div class="text-center py-4">
                <p class="text-sm font-medium text-gray-700">Bukan penanggung jawab tahap ini</p>
                <p class="text-xs text-gray-500 mt-1">Hanya pemutus yang ditetapkan untuk tahap ini yang dapat mengisi penilaian wawancara.</p>
            </div>
        @else
            @include('vacancies.partials._ringkasan-hasil-wawancara', ['result' => $result])
        @endif
    @else
        <form
            method="POST"
            action="{{ $isEdit
                ? route('lowongan.wawancara.keputusan.update', [$lowongan, $application, $stage])
                : route('lowongan.wawancara.keputusan', [$lowongan, $application]) }}"
            x-data="{ keputusan: @js($selectedKeputusan ?? '') }"
        >
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <input type="hidden" name="stage_id" value="{{ $stage->id }}">

            @php $ratingIndex = 0; @endphp
            @foreach ($assignedTemplates as $template)
                <div class="mb-4 space-y-3">
                    <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide">{{ $template->nama }}</p>
                    @foreach ($template->items as $item)
                        @php
                            $ratingKey = $template->id.'|'.$item->teks;
                            $oldRating = $oldBelongsToThisForm ? old("ratings.{$ratingIndex}.nilai") : null;
                            $selectedRating = $oldRating !== null
                                ? (string) $oldRating
                                : (string) ($savedRatings[$ratingKey] ?? '');
                        @endphp
                        <div>
                            <label class="block text-xs text-gray-700 mb-1">
                                {{ $item->teks }}
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="hidden" name="ratings[{{ $ratingIndex }}][interview_template_id]" value="{{ $template->id }}">
                            <input type="hidden" name="ratings[{{ $ratingIndex }}][nama_kriteria]" value="{{ $item->teks }}">
                            <div class="flex items-center gap-2">
                                @for ($i = 1; $i <= 3; $i++)
                                    <label class="flex flex-col items-center cursor-pointer">
                                        <input
                                            type="radio"
                                            name="ratings[{{ $ratingIndex }}][nilai]"
                                            value="{{ $i }}"
                                            class="sr-only peer"
                                            @if ($selectedRating === (string) $i) checked @endif
                                            required
                                        >
                                        <span class="w-8 h-8 flex items-center justify-center text-sm font-semibold rounded-full border border-gray-200 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:border-primary/40 transition-colors cursor-pointer">
                                            {{ $i }}
                                        </span>
                                    </label>
                                @endfor
                            </div>
                            @error("ratings.{$ratingIndex}.nilai")
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        @php $ratingIndex++ @endphp
                    @endforeach
                </div>
            @endforeach

            @if ($assignedReadinessTemplates->isNotEmpty())
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-700 mb-3">Pertanyaan Kesiapan</p>
                    @php $readinessIndex = 0; @endphp
                    @foreach ($assignedReadinessTemplates as $readinessTemplate)
                        <div class="mb-3 space-y-2">
                            <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide">{{ $readinessTemplate->nama }}</p>
                            @foreach ($readinessTemplate->items as $readinessItem)
                                @php
                                    $readinessKey = $readinessTemplate->id.'|'.$readinessItem->teks;
                                    $oldJawaban = $oldBelongsToThisForm ? old("readiness_answers.{$readinessIndex}.jawaban") : null;
                                    $selectedJawaban = $oldJawaban !== null
                                        ? (string) $oldJawaban
                                        : (string) ($savedReadiness[$readinessKey] ?? '');
                                @endphp
                                <div>
                                    <label class="block text-xs text-gray-700 mb-1">
                                        {{ $readinessItem->teks }}
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="hidden" name="readiness_answers[{{ $readinessIndex }}][interview_template_id]" value="{{ $readinessTemplate->id }}">
                                    <input type="hidden" name="readiness_answers[{{ $readinessIndex }}][pertanyaan]" value="{{ $readinessItem->teks }}">
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="readiness_answers[{{ $readinessIndex }}][jawaban]"
                                                value="1"
                                                class="w-4 h-4 accent-primary"
                                                @if ($selectedJawaban === '1') checked @endif
                                                required
                                            >
                                            <span class="text-xs text-gray-700">Ya</span>
                                        </label>
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input
                                                type="radio"
                                                name="readiness_answers[{{ $readinessIndex }}][jawaban]"
                                                value="0"
                                                class="w-4 h-4 accent-primary"
                                                @if ($selectedJawaban === '0') checked @endif
                                                required
                                            >
                                            <span class="text-xs text-gray-700">Tidak</span>
                                        </label>
                                    </div>
                                </div>
                                @php $readinessIndex++ @endphp
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($decisionLocked)
                <div class="mb-4">
                    <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Keputusan</p>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium {{ $decisionMeta[0] }}">
                            {{ $decisionMeta[1] }}
                        </span>
                        <p class="text-[11px] text-gray-500">Keputusan tidak dapat diubah. Nilai, jawaban kesiapan, dan catatan masih dapat diperbarui.</p>
                    </div>
                    <input type="hidden" name="keputusan" value="{{ $selectedKeputusan }}">
                </div>
            @else
                <div class="space-y-2 mb-4">
                    @if ($isEdit)
                        <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                            Keputusan
                        </label>
                        <select
                            name="keputusan"
                            x-model="keputusan"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 bg-white"
                        >
                            @foreach (['lulus' => 'Lulus', 'reserved' => 'Tunda', 'gagal' => 'Gagal'] as $value => $label)
                                <option value="{{ $value }}" @if ($selectedKeputusan === $value) selected @endif>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        @foreach (['lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700'], 'reserved' => ['Tunda', 'bg-amber-50 border-amber-300 text-amber-700'], 'gagal' => ['Gagal', 'bg-red-50 border-red-300 text-red-700']] as $value => $config)
                            <label
                                class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ease-out duration-150"
                                :class="keputusan === '{{ $value }}' ? '{{ $config[1] }}' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    name="keputusan"
                                    value="{{ $value }}"
                                    x-model="keputusan"
                                    class="w-4 h-4 accent-current"
                                    @if ($selectedKeputusan === $value) checked @endif
                                >
                                <span class="text-sm font-medium" :class="keputusan === '{{ $value }}' ? '' : 'text-gray-700'">{{ $config[0] }}</span>
                            </label>
                        @endforeach
                    @endif
                </div>
            @endif

            @error('keputusan')
                <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
            @enderror

            <div class="mb-4">
                <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                    Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                </label>
                <textarea
                    name="catatan"
                    rows="3"
                    placeholder="Catatan penilaian wawancara..."
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                >{{ $selectedCatatan }}</textarea>
            </div>

            <button
                type="submit"
                class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                x-bind:disabled="!keputusan"
            >
                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Keputusan' }}
            </button>
        </form>
    @endif

    @if ($isEdit)
        </div> {{-- close accordion body --}}
    @endif
</div>
