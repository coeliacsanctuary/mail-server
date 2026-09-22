@props(['properties'])

@php
if(!is_array($properties['content'] ?? '')) {
    $properties['content'] = explode("\n", $properties['content'] ?? '');
}

$align = \App\Editor\Support\Alignment::fromName($properties['align'] ?? null);
$alignAttribute = $align === \App\Editor\Support\Alignment::Left ? '' : ' align="'.$align->value.'"';
@endphp

@foreach($properties['content'] as $line)
    <mj-text mj-class="inner" css-class="blue-links"{!! $alignAttribute !!}>{!! $line !!}</mj-text>
@endforeach
