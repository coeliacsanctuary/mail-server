@props(['properties'])

@isset($properties['content'])
    <mj-image @if(!empty($properties['link'])) href="{{ trim($properties['link']) }}" @endif src="{{ $properties['content'] }}" alt="{{ $properties['alt'] ?? '' }}" fluid-on-mobile="true"></mj-image>
@endisset
