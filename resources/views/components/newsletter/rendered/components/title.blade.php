@props(['properties'])

@php
    $align = \App\Editor\Support\Alignment::fromName($properties['align'] ?? null, \App\Editor\Support\Alignment::Center);
@endphp

<mj-text align="{{ $align->value }}" css-class="blue-links">
    <h1>
        @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
            {{ $properties['content'] ?? '[MISSING TITLE]' }}
        @if(filled($properties['link'] ?? null)) </a>@endif
    </h1>
</mj-text>
