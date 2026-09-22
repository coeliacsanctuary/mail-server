@props(['properties', 'block'])

<mj-image href="{{ trim($properties['link'] ?? '') }}" src="{{ $properties['content'] ?? '' }}" alt="{{ $properties['alt'] ?? '' }}" fluid-on-mobile="true"></mj-image>

@if(isset($properties['label']) && $properties['label'] !== '')
    <x-newsletter.rendered.button :href="trim($properties['link'] ?? '')" :block="$block" padding="10px 0">
        {{ $properties['label'] }}
    </x-newsletter.rendered.button>
@endif
