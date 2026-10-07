@props(['title' => 'Tes - RS Azra', 'heading' => 'Tes', 'position' => null, 'meta' => null])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-paper text-ink antialiased">
    <header class="sticky top-0 z-50 w-full border-b border-line bg-white">
        <div class="ui-shell flex items-center justify-between gap-4 py-3">
            <div class="min-w-0">
                <p class="text-[14px] font-semibold text-ink">{{ $heading }}</p>
                @if ($position)
                    <p class="truncate text-[13px] text-ink-3">{{ $position }}</p>
                @endif
            </div>
            @if ($meta)
                <div class="shrink-0 text-[13px] font-semibold text-ink-2">{{ $meta }}</div>
            @endif
        </div>
    </header>

    <main class="ui-shell w-full py-6 sm:py-8">
        {{ $slot }}
    </main>

</body>
</html>
