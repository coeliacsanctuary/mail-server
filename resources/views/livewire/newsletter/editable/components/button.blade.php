<div class="button-editor">
    <div class="text-xs uppercase font-semibold mb-2">Button</div>

    <div
        class="button-mockup {{ $block === 'single' ? 'button-mockup--large' : '' }}"
        style="background-color: {{ $this->colour()->background() }}; color: {{ $this->colour()->text() }}; text-align: {{ $textAlign }};"
    >
        <input
            type="text"
            wire:model.live.blur="label"
            placeholder="Label..."
            class="button-mockup-input"
            aria-label="{{ __mc('Button label') }}"
        />
    </div>

    <div class="component-props">
        <div class="component-props-set">
            @foreach(\App\Editor\Support\ButtonColour::cases() as $colour)
                <button
                    type="button"
                    wire:key="{{ $componentId }}-colour-{{ $colour->value }}"
                    wire:click="setBackground('{{ $colour->value }}')"
                    class="component-swatch {{ $background === $colour->value ? 'component-swatch--on' : '' }}"
                    style="background-color: {{ $colour->background() }};"
                    x-tooltip="'{{ $colour->label() }}'"
                    aria-label="{{ $colour->label() }}"
                ></button>
            @endforeach
        </div>

        <div class="component-props-set">
            @foreach(['left' => 'bars-3-bottom-left', 'center' => 'bars-3', 'right' => 'bars-3-bottom-right'] as $alignment => $icon)
                <button
                    type="button"
                    wire:key="{{ $componentId }}-align-{{ $alignment }}"
                    wire:click="setTextAlign('{{ $alignment }}')"
                    class="component-prop {{ $textAlign === $alignment ? 'component-prop--on' : '' }}"
                    x-tooltip="'{{ __mc('Align :alignment', ['alignment' => $alignment]) }}'"
                    aria-label="{{ __mc('Align :alignment', ['alignment' => $alignment]) }}"
                >
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="w-4 h-4" />
                </button>
            @endforeach
        </div>
    </div>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link"/>
    </div>
</div>
