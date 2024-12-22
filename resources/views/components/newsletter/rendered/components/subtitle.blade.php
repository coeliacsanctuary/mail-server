@props(['properties'])

<mj-column>
    <mj-text mj-class="inner" css-class="blue-links">
        <h3>
            @isset($properties['link'])<a href="{{ trim($properties['link']) }}"> @endisset
                {{ $properties['content'] ?? '[MISSING SUBTITLE]' }}
            @isset($properties['link']) </a>@endisset
        </h3>
    </mj-text>
</mj-column>
