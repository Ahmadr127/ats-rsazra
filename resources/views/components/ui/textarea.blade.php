@props(['label' => null, 'hint' => null, 'id' => null])

@php
    $inputId = $id ?? $attributes->get('name');
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $inputId }}" class="ui-label mb-1.5 block">{{ $label }}</label>
    @endif
    <textarea id="{{ $inputId }}" {{ $attributes->merge(['class' => 'ui-textarea focus-ring']) }}>{{ $slot }}</textarea>
    @if ($hint)
        <p class="ui-help mt-1.5">{{ $hint }}</p>
    @endif
    @error($attributes->get('name'))
        <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
    @enderror
</div>
