@props(['componentId', 'selected', 'action' => 'setColour'])

<div class="component-props-set">
    @foreach(\App\Editor\Support\BrandColour::cases() as $colour)
        <button
            type="button"
            wire:key="{{ $componentId }}-colour-{{ $colour->value }}"
            wire:click="{{ $action }}('{{ $colour->value }}')"
            class="component-swatch {{ $selected === $colour->value ? 'component-swatch--on' : '' }}"
            style="background-color: {{ $colour->background() }};"
            x-tooltip="'{{ $colour->label() }}'"
            aria-label="{{ $colour->label() }}"
        ></button>
    @endforeach
</div>
