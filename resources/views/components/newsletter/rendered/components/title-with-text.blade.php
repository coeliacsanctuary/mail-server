@props(['properties', 'block'])

<mj-column css-class="blue-links">
    <mj-text align="center">
        <h1>
            @isset($properties['link'])<a href="{{ trim($properties['link']) }}"> @endisset
                {{ $properties['title'] ?? '[MISSING TITLE]' }}
            @isset($properties['link']) </a>@endisset
        </h1>
    </mj-text>

    @foreach(explode("\n", $properties['content'] ?? '') as $line)
        <mj-text mj-class="inner">{!! $line !!}</mj-text>
    @endforeach
</mj-column>
