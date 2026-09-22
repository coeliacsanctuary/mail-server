@props(['properties', 'block'])

<mj-column css-class="blue-links">
    <mj-text align="center">
        <h1>
            @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
                {{ $properties['title'] ?? '[MISSING TITLE]' }}
            @if(filled($properties['link'] ?? null)) </a>@endif
        </h1>
    </mj-text>

    @foreach(explode("\n", $properties['content'] ?? '') as $line)
        <mj-text mj-class="inner">{!! $line !!}</mj-text>
    @endforeach
</mj-column>
