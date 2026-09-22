@props(['properties', 'block'])

<x-newsletter.rendered.button :href="trim($properties['link'] ?? '')" :block="$block">
    {{ $properties['content'] ?? '' }}
</x-newsletter.rendered.button>
