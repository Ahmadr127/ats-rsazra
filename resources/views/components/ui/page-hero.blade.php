@props(['eyebrow' => null, 'title' => null, 'lede' => null, 'compact' => false, 'class' => ''])

<section {{ $attributes->merge(['class' => 'w-full border-b border-line bg-white ' . $class]) }}>
    <div class="ui-shell {{ $compact ? 'py-5' : 'py-8 sm:py-10' }}">
        @if ($eyebrow)
            <p class="ui-eyebrow mb-3">{{ $eyebrow }}</p>
        @endif
        @if ($title)
            <h1 class="ui-title">{{ $title }}</h1>
        @endif
        @if ($lede)
            <p class="ui-subtitle mt-2 max-w-3xl {{ $compact ? 'line-clamp-2' : '' }}">{{ $lede }}</p>
        @endif
        {{ $slot }}
    </div>
</section>
