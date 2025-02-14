@props(['properties', 'block', 'position'])

@php
    $class = 'full';

    if($block === 'double') {
        $class = "double-{$position}";
    }

    if($block === 'triple') {
        $class = "triple-{$position}";
    }
@endphp

<mj-column css-class="{{ $class }}">
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
