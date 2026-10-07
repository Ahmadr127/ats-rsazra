@props(['label' => null, 'id' => null])

@php
    $inputId = $id ?? $attributes->get('name') . '-' . $attributes->get('value');
@endphp

<label class="flex cursor-pointer items-center gap-2.5 text-[14px] text-ink-2">
    <input id="{{ $inputId }}" type="radio" {{ $attributes->merge(['class' => 'h-4 w-4 border-line text-primary focus:ring-primary/30']) }}>
    <span>
        @if ($label)
            {{ $label }}
        @else
            {{ $slot }}
        @endif
    </span>
</label>
