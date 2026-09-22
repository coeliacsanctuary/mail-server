@props(['block', 'position'])

@php
    $cssClass = in_array($block, ['double', 'triple'], true)
        ? "{$block}-{$position}"
        : 'full';
@endphp

<mj-column css-class="{{ $cssClass }}">
    {{ $slot }}
</mj-column>
