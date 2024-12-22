@props(['properties', 'block', 'position'])

<mj-column
    @if(($block === 'double' || $block === 'triple') && $position === 0)
        padding-right="10px"
    @endif

    @if($block === 'triple' && $position === 1)
        padding-right="10px"
    @endif
>
    <mj-text css-class="blue-links">
        <h3 style="font-size:1.5rem" class="blue-links">
            <a href="{{ $properties['link'] ?? '' }}"
               css-class="blue-links">{{ $properties['name'] ?? '' }}</a>
        </h3>
    </mj-text>

    <mj-text css-class="blue-links" padding-top="4px">
        <h4 style="margin:0">
            {{ $properties['location'] ?? '' }}
        </h4>
    </mj-text>

    <mj-text css-class="blue-links" padding-top="10px">
        {{ $properties['info'] ?? '' }}
    </mj-text>

    @if(($properties['reviews']['number'] ?? 0) > 0)
        <mj-text css-class="blue-links" padding-top="10px">
            Rated <strong style="font-weight: bold">{{ $properties['reviews']['average'] }} stars</strong>
            from {{ $properties['reviews']['number'] }} ratings.
        </mj-text>
    @endif
</mj-column>
