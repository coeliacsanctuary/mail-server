@props(['properties', 'block'])

@php
    $colour = \App\Editor\Support\BrandColour::fromName($properties['background'] ?? null);
    $textAlign = $properties['text_align'] ?? null;
@endphp

<x-newsletter.rendered.button
    :href="trim($properties['link'] ?? '')"
    :block="$block"
    :colour="$colour === \App\Editor\Support\BrandColour::default() ? null : $colour"
    :text-align="in_array($textAlign, ['center', 'right'], true) ? $textAlign : null"
>
    {{ $properties['content'] ?? '' }}
</x-newsletter.rendered.button>
