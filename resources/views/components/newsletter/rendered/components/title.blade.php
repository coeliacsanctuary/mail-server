@props(['properties'])

<mj-column>
    <mj-text align="center" css-class="blue-links">
        <h1>
            @isset($properties['link'])<a href="{{ trim($properties['link'])}}"> @endisset
                {{ $properties['content'] ?? '[MISSING TITLE]' }}
            @isset($properties['link']) </a>@endisset
        </h1>
    </mj-text>
</mj-column>
