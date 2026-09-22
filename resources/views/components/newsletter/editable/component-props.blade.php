@props(['stacked' => false])

<div class="component-props {{ $stacked ? 'component-props--stacked' : '' }}">
    {{ $slot }}
</div>
