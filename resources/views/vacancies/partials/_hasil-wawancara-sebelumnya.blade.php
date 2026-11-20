{{-- Hasil wawancara tahap sebelumnya. Tetap dapat diedit selama tahap berikutnya belum terisi. --}}
{{-- Variables: $application, $lowongan, $interviewStages, $interviewTemplatesByStage. --}}

@php
    $authUser = auth()->user();
@endphp

@if ($interviewStages->isNotEmpty())
    <div class="space-y-4">
        <h2 class="text-sm font-semibold text-gray-800">Hasil Wawancara Sebelumnya</h2>

        @foreach ($interviewStages as $interviewStage)
            @php
                $stageTemplates = $interviewTemplatesByStage->get($interviewStage->key, [
                    'ratings' => collect(),
                    'readiness' => collect(),
                ]);
                $stagePermission = \App\Support\InterviewStageMap::decidePermissionForStage($interviewStage->key);
                $canEditStage = $interviewStage->isInterviewEditable()
                    && $stagePermission
                    && $authUser->hasPermission($stagePermission)
                    && ($interviewStage->key !== \App\Support\InterviewStageMap::USER || $interviewStage->interviewer_id === $authUser->id)
                    && ($stageTemplates['ratings'] ?? collect())->isNotEmpty();

                $subheading = $interviewStage->nama;
                if ($interviewStage->jadwal) {
                    $subheading .= ' · '.$interviewStage->jadwal->translatedFormat('d M Y, H:i');
                }
            @endphp

            @include('vacancies.partials._form-penilaian-wawancara', [
                'application' => $application,
                'lowongan' => $lowongan,
                'stage' => $interviewStage,
                'assignedTemplates' => $stageTemplates['ratings'] ?? collect(),
                'assignedReadinessTemplates' => $stageTemplates['readiness'] ?? collect(),
                'result' => $interviewStage->activeInterviewResult,
                'canEdit' => $canEditStage,
                'subheading' => $subheading,
            ])
        @endforeach
    </div>
@endif
