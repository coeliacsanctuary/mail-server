@props(['properties', 'block', 'position'])

@php
    $class = 'full';

    if($block === 'double') {
        $class = "double-{$position}";
    }

    if($block === 'triple') {
        $class = "triple-{$position}";
    }
@endphp

<mj-column css-class="{{ $class }}">
    @if($block === 'single')
        <mj-text mj-class="inner blue-links">
            <h2 class="blue-links">
                <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
            </h2>
        </mj-text>
    @endif

    <mj-image href="{{ $properties['link'] ?? '' }}" src="{{ $properties['image'] ?? '' }}" fluid-on-mobile="true"></mj-image>

    <mj-text css-class="blue-links" padding="10px 0">
        <h3>
            <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
        </h3>
    </mj-text>

    <mj-text css-class="blue-links">
        {!! $properties['description'] ?? '' !!}
    </mj-text>

    <mj-button
        href="{{ $properties['link'] ?? '' }}"
        padding="10px 0"
        @if($block === 'single') border-radius="6px" font-size="20px" @endif
    >
        Read more
    </mj-button>
</mj-column>
