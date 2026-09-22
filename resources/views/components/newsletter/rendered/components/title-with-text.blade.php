@props(['properties', 'block'])

<mj-text align="center" css-class="blue-links">
    <h1>
        @if(filled($properties['link'] ?? null))<a href="{{ trim($properties['link']) }}"> @endif
            {{ $properties['title'] ?? '[MISSING TITLE]' }}
        @if(filled($properties['link'] ?? null)) </a>@endif
    </h1>
</mj-text>

@foreach(explode("\n", $properties['content'] ?? '') as $line)
    <mj-text mj-class="inner" css-class="blue-links">{!! $line !!}</mj-text>
@endforeach
