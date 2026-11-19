{{-- Aksi Tahap: Tes MBTI --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

@include('vacancies.partials._aksi-tes-eksternal', [
    'submission' => $application->mbtiSubmission,
    'testLabel' => 'MBTI',
    'sendRoute' => 'lowongan.lamaran.tes-mbti.kirim',
])
