<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ATS RS Azra' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <div class="flex min-h-screen w-full flex-col lg:flex-row">
        <div class="flex w-full flex-col justify-between bg-primary px-6 py-8 sm:px-10 lg:w-[42%] lg:px-12 lg:py-12">
            <p class="flex items-center gap-2.5 text-[13px] font-semibold text-white/90">
                <img src="{{ asset('images/logo.png') }}" alt="RS Azra" class="h-8 w-8 object-contain">
                Rumah Sakit Azra
            </p>
            <div class="py-8">
                <p class="ui-label text-white/60!">Sistem Kepegawaian</p>
                <h1 class="mt-3 text-[25px] font-bold leading-tight text-white">Applicant Tracking System</h1>
                <p class="mt-3 max-w-md text-[14px] leading-relaxed text-white/70">Kelola rekrutmen dan data kepegawaian Rumah Sakit Azra dari satu platform yang terintegrasi.</p>
            </div>
            <ul class="space-y-2 text-[13px] text-white/70">
                <li>Manajemen rekrutmen terpadu</li>
                <li>Kontrol akses berbasis peran</li>
                <li>Data karyawan terpusat dan aman</li>
            </ul>
        </div>

        <div class="flex w-full flex-1 items-center justify-center px-4 py-10 sm:px-6 lg:px-10">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </div>
    </div>

</body>
</html>
