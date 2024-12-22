@props(['properties'])

<mj-column>
    <mj-image @if(!empty($properties['link'])) href="{{ trim($properties['link']) }}" @endif src="{{ $properties['content'] }}" fluid-on-mobile="true"></mj-image>
</mj-column>
