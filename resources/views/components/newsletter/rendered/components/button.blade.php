@props(['properties', 'block'])

<mj-column>
    <x-newsletter.rendered.button :href="trim($properties['link'] ?? '')" :block="$block">
        {{ $properties['content'] ?? '' }}
    </x-newsletter.rendered.button>
</mj-column>
