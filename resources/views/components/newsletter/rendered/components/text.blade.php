@props(['properties'])

@php
if(!is_array($properties['content'] ?? '')) {
    $properties['content'] = explode("\n", $properties['content'] ?? '');
}
@endphp

<mj-column css-class="blue-links">
    @foreach($properties['content'] as $line)
        <mj-text mj-class="inner">{!! $line !!}</mj-text>
    @endforeach
</mj-column>
