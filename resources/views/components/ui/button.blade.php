@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'class' => ''])

@php
    $variantClass = $variant === 'secondary' ? 'ui-btn-secondary' : 'ui-btn-primary';
    $classes = 'ui-btn ' . $variantClass . ' ' . $class;
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
