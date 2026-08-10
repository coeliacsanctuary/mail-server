@props(['properties'])

{{-- filled(), not isset(): a link that was typed and then cleared is stored as '', and isset('') is true. --}}
<mj-column>
    <mj-text mj-class="inner" css-class="blue-links">
        <h3>
            @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
                {{ $properties['content'] ?? '[MISSING SUBTITLE]' }}
            @if(filled($properties['link'] ?? null)) </a>@endif
        </h3>
    </mj-text>
</mj-column>
