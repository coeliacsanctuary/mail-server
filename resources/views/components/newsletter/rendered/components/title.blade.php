@props(['properties'])

<mj-column>
    <mj-text mj-class="inner" padding-bottom="10px" css-class="blue-links">
        <h2>
            @isset($properties['link'])<a href="{{ trim($properties['link'])}}"> @endisset
                {{ $properties['content'] ?? '[MISSING TITLE]' }}
            @isset($properties['link']) </a>@endisset
        </h2>
    </mj-text>
</mj-column>
