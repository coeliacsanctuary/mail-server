@props(['blockId', 'first', 'last'])

{{--
    Positioned by .block-actions into the seam above the block, which needs no
    room beside the email column. It used to hang off a negative offset, which
    overlapped the block's own inputs by 47px at every width and escaped the
    card on anything narrower than ~1440px.
--}}
<div wire:key="{{ $blockId }}-actions" class="block-actions">
    <div class="block-actions-bar" wire:key="{{ $blockId }}-actions-inner">
        {{--
            Dragging is restricted to this handle so that text selection inside
            the block's inputs and textareas still works. The up/down chevrons
            stay: Sortable has no keyboard support, so they are the only
            keyboard route to reordering.

            Deliberately not a <button>: a focusable handle competes with
            Sortable's own mousedown handling, and it is not a keyboard
            affordance anyway — the chevrons are.
        --}}
        <div
            class="block-action block-action--drag"
            wire:key="{{ $blockId }}-actions-drag"
            wire:sort:handle
            x-tooltip="'{{ __mc('Drag to reorder') }}'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
            </svg>
        </div>

        <button
            type="button"
            class="block-action @if($first) block-action--disabled @endif"
            wire:key="{{ $blockId }}-actions-up"
            @unless($first)
                wire:click="moveBlock('{{ $blockId }}', 'up')"
                x-tooltip="'{{ __mc('Move block up') }}'"
            @endunless
            @disabled($first)
            aria-label="{{ __mc('Move block up') }}"
        >
            <x-heroicon-o-chevron-up class="w-6 h-6" />
        </button>

        <button
            type="button"
            class="block-action @if($last) block-action--disabled @endif"
            wire:key="{{ $blockId }}-actions-down"
            @unless($last)
                wire:click="moveBlock('{{ $blockId }}', 'down')"
                x-tooltip="'{{ __mc('Move block down') }}'"
            @endunless
            @disabled($last)
            aria-label="{{ __mc('Move block down') }}"
        >
            <x-heroicon-o-chevron-down class="w-6 h-6" />
        </button>

        <button
            type="button"
            class="block-action"
            wire:key="{{ $blockId }}-actions-duplicate"
            wire:click="duplicateBlock('{{ $blockId }}')"
            x-tooltip="'{{ __mc('Duplicate block') }}'"
            aria-label="{{ __mc('Duplicate block') }}"
        >
            <x-heroicon-o-document-duplicate class="w-6 h-6" />
        </button>

        {{-- There is no undo in this editor, and a block can hold three components. --}}
        <x-mailcoach::confirm-button
            class="block-action"
            danger
            :confirm-text="__mc('Delete this block and everything in it?')"
            :confirm-label="__mc('Delete block')"
            on-confirm="() => $wire.deleteBlock('{{ $blockId }}')"
            wire:key="{{ $blockId }}-actions-delete"
            x-tooltip="'{{ __mc('Delete block') }}'"
        >
            <x-heroicon-o-trash class="w-6 h-6" />
            <span class="visually-hidden">{{ __mc('Delete block') }}</span>
        </x-mailcoach::confirm-button>

        <button
            type="button"
            class="block-action"
            wire:key="{{ $blockId }}-actions-add"
            x-on:click="window._addBlockBelow='{{ $blockId }}'; $dispatch('open-modal', { id: 'add-block' })"
            x-tooltip="'{{ __mc('Add a block below') }}'"
            aria-label="{{ __mc('Add a block below') }}"
        >
            <x-heroicon-o-plus class="w-6 h-6" />
        </button>
    </div>
</div>
