@props(['properties', 'block'])

@if(filled($properties['content'] ?? null))
    @if($block === 'single')
        <mj-text mj-class="inner blue-links">
            <h2 class="blue-links">
                <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
            </h2>
        </mj-text>
    @endif

    <mj-image href="{{ $properties['link'] ?? '' }}" src="{{ $properties['image'] ?? '' }}" css-class="fluid-img" fluid-on-mobile="true"></mj-image>

    <mj-text css-class="blue-links" padding="10px 0">
        <h3>
            <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
        </h3>
    </mj-text>

    <mj-text css-class="blue-links">
        {!! $properties['description'] ?? '' !!}
    </mj-text>

    <x-newsletter.rendered.button :href="$properties['link'] ?? ''" :block="$block" padding="10px 0">
        Read more
    </x-newsletter.rendered.button>
@endif
