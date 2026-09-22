@props(['properties', 'block'])

<mj-column>
    <mj-image href="{{ trim($properties['link'] ?? '') }}" src="{{ $properties['content'] ?? '' }}" alt="{{ $properties['alt'] ?? '' }}" fluid-on-width="true"></mj-image>

    @if(isset($properties['label']) && $properties['label'] !== '')
        <x-newsletter.rendered.button :href="trim($properties['link'] ?? '')" :block="$block" padding="10px 0">
            {{ $properties['label'] }}
        </x-newsletter.rendered.button>
    @endif
</mj-column>
