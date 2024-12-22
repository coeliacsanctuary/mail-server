@props(['properties', 'block'])

<mj-column>
    @foreach(explode("\n", $properties['content']) as $line)
        <mj-text mj-class="inner" css-class="blue-links">{!! $line !!}</mj-text>
    @endforeach

    <mj-button href="{{ trim($properties['link']) }}" padding="10px 0" @if($block === 'single') border-radius="6px" font-size="20px" @endif>
        {{ $properties['label'] }}
    </mj-button>
</mj-column>
