@props(['properties'])

{{-- filled(), not isset(): a link that was typed and then cleared is stored as '', and isset('') is true. --}}
<mj-column>
    <mj-text align="center" css-class="blue-links">
        <h1>
            @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
                {{ $properties['content'] ?? '[MISSING TITLE]' }}
            @if(filled($properties['link'] ?? null)) </a>@endif
        </h1>
    </mj-text>
</mj-column>
