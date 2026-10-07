@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'ui-card p-5 sm:p-6 ' . $class]) }}>
    {{ $slot }}
</div>
