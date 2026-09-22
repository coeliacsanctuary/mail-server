@props(['properties', 'block'])

<mj-column>
    @foreach(explode("\n", $properties['content'] ?? '') as $line)
        <mj-text mj-class="inner" css-class="blue-links">{!! $line !!}</mj-text>
    @endforeach

    <x-newsletter.rendered.button :href="trim($properties['link'] ?? '')" :block="$block" padding="10px 0">
        {{ $properties['label'] ?? '' }}
    </x-newsletter.rendered.button>
</mj-column>
