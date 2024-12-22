@props(['properties', 'block'])

<mj-column>
    <mj-button href="{{ trim($properties['link']) }}" @if($block === 'single') border-radius="6px" font-size="20px" @endif>
        {{ $properties['content'] }}
    </mj-button>
</mj-column>
