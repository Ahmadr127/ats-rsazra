{{-- Ringkasan read-only hasil wawancara. --}}
{{-- Variables: $result (InterviewResult). --}}

@php
    $ratingGroups = $result->ratings->groupBy('interview_template_id');
    $readinessGroups = $result->readinessAnswers->groupBy('interview_template_id');
    $hasDetails = $ratingGroups->isNotEmpty() || $readinessGroups->isNotEmpty() || filled($result->catatan);
@endphp

@if (! $hasDetails)
    <p class="text-xs text-gray-400 text-center py-2">Belum ada detail penilaian.</p>
@else
    @if ($ratingGroups->isNotEmpty())
        <div class="space-y-3 mb-3">
            @foreach ($ratingGroups as $groupRatings)
                @php $templateName = $groupRatings->first()->interviewTemplate?->nama ?? 'Template Dihapus'; @endphp
                <div>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $templateName }}</p>
                    @foreach ($groupRatings as $rating)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600">{{ $rating->nama_kriteria }}</span>
                            <span class="font-medium text-gray-800">{{ $rating->nilai }}/3</span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    @if ($readinessGroups->isNotEmpty())
        <div class="space-y-3 mb-3">
            @foreach ($readinessGroups as $groupAnswers)
                @php $templateName = $groupAnswers->first()->interviewTemplate?->nama ?? 'Template Dihapus'; @endphp
                <div>
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $templateName }}</p>
                    @foreach ($groupAnswers as $answer)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600">{{ $answer->pertanyaan }}</span>
                            <span class="font-medium {{ $answer->jawaban ? 'text-green-600' : 'text-red-500' }}">
                                {{ $answer->jawaban ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif

    @if (filled($result->catatan))
        <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">{{ $result->catatan }}</p>
    @endif
@endif
