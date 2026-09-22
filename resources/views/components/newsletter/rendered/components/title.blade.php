@props(['properties'])

<mj-text align="center" css-class="blue-links">
    <h1>
        @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
            {{ $properties['content'] ?? '[MISSING TITLE]' }}
        @if(filled($properties['link'] ?? null)) </a>@endif
    </h1>
</mj-text>
