@props(['label' => null, 'id' => null])

@php
    $inputId = $id ?? $attributes->get('name');
@endphp

<label class="flex cursor-pointer items-center gap-2.5 text-[14px] text-ink-2">
    <input id="{{ $inputId }}" type="checkbox" {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-line text-primary focus:ring-primary/30']) }}>
    @if ($label)
        <span>{{ $label }}</span>
    @else
        <span>{{ $slot }}</span>
    @endif
</label>
