@props(['properties'])

@php
    $align = \App\Editor\Support\Alignment::fromName($properties['align'] ?? null);
    $alignAttribute = $align === \App\Editor\Support\Alignment::Left ? '' : ' align="'.$align->value.'"';
@endphp

<mj-text mj-class="inner" css-class="blue-links"{!! $alignAttribute !!}>
    <h3>
        @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
            {{ $properties['content'] ?? '[MISSING SUBTITLE]' }}
        @if(filled($properties['link'] ?? null)) </a>@endif
    </h3>
</mj-text>
