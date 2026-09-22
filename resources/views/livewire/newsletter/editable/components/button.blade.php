<div class="component-editor">
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

    <x-newsletter.editable.component-props>
        <x-newsletter.editable.colour-swatches
            :component-id="$componentId"
            :selected="$background"
            action="setBackground"
        />

        <x-newsletter.editable.alignment-buttons
            :component-id="$componentId"
            :selected="$textAlign"
            action="setTextAlign"
        />
    </x-newsletter.editable.component-props>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link"/>
    </div>
</div>
