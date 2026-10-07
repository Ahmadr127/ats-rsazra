@props(['label' => null, 'hint' => null, 'id' => null])

@php
    $inputId = $id ?? $attributes->get('name');
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $inputId }}" class="ui-label mb-1.5 block">{{ $label }}</label>
    @endif
    <select id="{{ $inputId }}" {{ $attributes->merge(['class' => 'ui-select focus-ring']) }}>{{ $slot }}</select>
    @if ($hint)
        <p class="ui-help mt-1.5">{{ $hint }}</p>
    @endif
</div>
