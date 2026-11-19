{{-- Aksi Tahap: Tes DISC --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

@include('vacancies.partials._aksi-tes-eksternal', [
    'submission' => $application->discSubmission,
    'testLabel' => 'DISC',
    'sendRoute' => 'lowongan.lamaran.tes-disc.kirim',
])
