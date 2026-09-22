@props(['properties'])

<mj-text mj-class="inner" css-class="blue-links">
    <h3>
        @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
            {{ $properties['content'] ?? '[MISSING SUBTITLE]' }}
        @if(filled($properties['link'] ?? null)) </a>@endif
    </h3>
</mj-text>
