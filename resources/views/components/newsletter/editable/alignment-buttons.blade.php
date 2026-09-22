@props(['componentId', 'selected', 'action' => 'setAlign'])

<div class="component-props-set">
    @foreach(\App\Editor\Support\Alignment::cases() as $alignment)
        <button
            type="button"
            wire:key="{{ $componentId }}-align-{{ $alignment->value }}"
            wire:click="{{ $action }}('{{ $alignment->value }}')"
            class="component-prop {{ $selected === $alignment->value ? 'component-prop--on' : '' }}"
            x-tooltip="'{{ $alignment->label() }}'"
            aria-label="{{ $alignment->label() }}"
        >
            <x-dynamic-component :component="'heroicon-o-'.$alignment->icon()" class="w-4 h-4" />
        </button>
    @endforeach
</div>
