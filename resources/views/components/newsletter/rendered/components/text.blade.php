@props(['properties'])

@php
if(!is_array($properties['content'] ?? '')) {
    $properties['content'] = explode("\n", $properties['content'] ?? '');
}
@endphp

@foreach($properties['content'] as $line)
    <mj-text mj-class="inner" css-class="blue-links">{!! $line !!}</mj-text>
@endforeach
