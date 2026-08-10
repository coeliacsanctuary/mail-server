@props(['block', 'position'])

@php
    /**
     * Single-column blocks get "full"; double and triple get a positional
     * class the responsive rules in metas.blade.php key off. Anything else
     * falls back to "full", matching the original per-component snippet.
     */
    $cssClass = in_array($block, ['double', 'triple'], true)
        ? "{$block}-{$position}"
        : 'full';
@endphp

<mj-column css-class="{{ $cssClass }}">
    {{ $slot }}
</mj-column>
