@props(['tone' => 'info', 'class' => ''])

@php
    $tones = [
        'info' => 'bg-primary-50 text-primary-dark border border-primary/20',
        'success' => 'bg-[#f0f7e6] text-[#3a5c14] border border-[#c3db9e]',
        'warning' => 'bg-amber-50 text-amber-800 border border-amber-200',
        'danger' => 'bg-red-50 text-red-700 border border-red-200',
        'neutral' => 'bg-paper text-ink-2 border border-line',
    ];
    $toneClass = $tones[$tone] ?? $tones['info'];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[12px] font-semibold ' . $toneClass . ' ' . $class]) }}>{{ $slot }}</span>
