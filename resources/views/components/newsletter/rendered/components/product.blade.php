@props(['properties', 'block', 'position'])

@if(filled($properties['content'] ?? null))
    <x-newsletter.rendered.column :block="$block" :position="$position">
        @if($block === 'single')
            <mj-text mj-class="inner">
                <h2 class="blue-links">
                    <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
                </h2>
            </mj-text>
        @endif

        <mj-image href="{{ $properties['link'] ?? '' }}" src="{{ $properties['image'] ?? '' }}" fluid-on-mobile="true"
        ></mj-image>

        <mj-text css-class="blue-links" padding="10px 0">
            <h3>
                <a href="{{ $properties['link'] ?? '' }}">{{ $properties['title'] ?? '' }}</a>
            </h3>
        </mj-text>

        <mj-text css-class="blue-links" padding-bottom="10px">
            {!! $properties['description'] ?? '' !!}
        </mj-text>

        <mj-text css-class="blue-links" padding-bottom="10px">
            <h1>
                {{ $properties['price'] ?? '' }}
            </h1>
        </mj-text>

        <x-newsletter.rendered.button :href="$properties['link'] ?? ''" :block="$block" padding="10px 0">
            View Product
        </x-newsletter.rendered.button>
    </x-newsletter.rendered.column>
@endif
