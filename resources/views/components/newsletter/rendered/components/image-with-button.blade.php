@props(['properties', 'block'])

<mj-column>
    <mj-image href="{{ trim($properties['link']) }}" src="{{ $properties['content'] }}" fluid-on-width="true"></mj-image>

    <mj-button href="{{ trim($properties['link']) }}" padding="10px 0" @if($block === 'single') border-radius="6px" font-size="20px" @endif>
        {{ $label }}
    </mj-button>
</mj-column>
