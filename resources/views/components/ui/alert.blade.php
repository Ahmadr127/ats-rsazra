@props(['tone' => 'info', 'title' => null, 'class' => ''])

@php
    $tones = [
        'info' => 'border-primary/25 bg-primary-50 text-ink-2',
        'success' => 'border-[#c3db9e] bg-[#f0f7e6] text-ink-2',
        'warning' => 'border-amber-200 bg-amber-50 text-ink-2',
        'danger' => 'border-red-200 bg-red-50 text-ink-2',
    ];
    $toneClass = $tones[$tone] ?? $tones['info'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-xl border p-4 ' . $toneClass . ' ' . $class]) }} role="status">
    <div class="min-w-0">
        @if ($title)
            <p class="text-[14px] font-semibold text-ink">{{ $title }}</p>
        @endif
        <div class="text-[13px] leading-relaxed">{{ $slot }}</div>
    </div>
</div>
