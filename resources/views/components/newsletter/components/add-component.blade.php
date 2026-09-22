@props(['blockId', 'index'])

<div>
    <div class="flex items-center justify-center py-2"
         x-data="{
            addComponent(event) {
                if(window.activeBlock !== '{{ $blockId }}' || window.activeIndex !== {{ $index }}) {
                    return;
                }

                const component = event.detail[0];

                this.$dispatch('add-component-remote', ['{{ $blockId }}', component, {{ $index }}]);
                this.$dispatch('close-modal', { id: 'add-component' })
            }
         }"
         x-on:add-component.window="addComponent($event)"
         wire:key="{{ $blockId }}-{{ $index }}-add-inner"
    >
        <button
            type="button"
            class="editor-tile editor-tile--stacked"
            x-on:click="$dispatch('open-modal', { id: 'add-component' });window.activeBlock = '{{ $blockId }}';window.activeIndex = {{ $index }};"
            wire:key="{{ $blockId }}-{{ $index }}-add-content"
        >
            <x-heroicon-o-squares-plus class="w-6 h-6" />

            <span class="text-sm text-center">Add Component</span>
        </button>
    </div>
</div>
