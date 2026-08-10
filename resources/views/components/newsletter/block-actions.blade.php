@props(['blockId', 'index', 'first', 'last'])

<div
    wire:key="{{ $blockId }}-{{ $index }}-actions"
    class="block-actions absolute right-10 opacity-0 group-hover:opacity-100 transition h-full top-0 flex items-center" style="right: -200px"
>
    <div
        class="bg-gray-200 rounded border border-gray-600 flex"
        wire:key="{{ $blockId }}-{{ $index }}-actions-inner"
    >
        {{--
            Dragging is restricted to this handle so that text selection inside
            the block's inputs and textareas still works. The up/down chevrons
            stay: Sortable has no keyboard support, so they are the only
            keyboard route to reordering.
        --}}
        <div
            class="border-gray-600 py-1 px-2 hover:bg-gray-400/10"
            style="border-right-width: 1px; cursor: grab"
            wire:key="{{ $blockId }}-{{ $index }}-actions-drag"
            wire:sort:handle
            x-tooltip="'{{ __mc('Drag to reorder') }}'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
                <circle cx="9" cy="6" r="1.5"/><circle cx="15" cy="6" r="1.5"/>
                <circle cx="9" cy="12" r="1.5"/><circle cx="15" cy="12" r="1.5"/>
                <circle cx="9" cy="18" r="1.5"/><circle cx="15" cy="18" r="1.5"/>
            </svg>
        </div>
        <div
            class="border-gray-600 py-1 px-2 @if($first) text-gray-400 @else hover:bg-gray-400/10 @endif"
            style="border-right-width: 1px; @if($first) cursor: not-allowed @endif"
            @unless($first) wire:click="moveBlock('{{ $blockId }}', 'up')" @endunless
            wire:key="{{ $blockId }}-{{ $index }}-actions-up"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" />
            </svg>
        </div>
        <div
            class="border-gray-600 py-1 px-2 @if($last) text-gray-400 @else hover:bg-gray-400/10 @endif"
            style="border-right-width: 1px; @if($last) cursor: not-allowed @endif"
            @unless($last) wire:click="moveBlock('{{ $blockId }}', 'down')" @endunless
            wire:key="{{ $blockId }}-{{ $index }}-actions-down"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
        <div
            class="border-gray-600 py-1 px-2 hover:bg-gray-400/10 cursor-pointer" style="border-right-width: 1px"
            wire:key="{{ $blockId }}-{{ $index }}-actions-duplicate"
            wire:click="duplicateBlock('{{ $blockId }}')"
            x-tooltip="'{{ __mc('Duplicate block') }}'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
            </svg>
        </div>
        {{-- There is no undo in this editor, and a block can hold three components. --}}
        <x-mailcoach::confirm-button
            class="border-gray-600 py-1 px-2 hover:bg-gray-400/10 cursor-pointer"
            style="border-right-width: 1px"
            danger
            :confirm-text="__mc('Delete this block and everything in it?')"
            :confirm-label="__mc('Delete block')"
            on-confirm="() => $wire.deleteBlock('{{ $blockId }}')"
            wire:key="{{ $blockId }}-{{ $index }}-actions-delete"
            x-tooltip="'{{ __mc('Delete block') }}'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
            </svg>
        </x-mailcoach::confirm-button>
        <div
            class="py-1 px-2 hover:bg-gray-400/10"
            wire:key="{{ $blockId }}-{{ $index }}-actions-add"
            x-on:click="window._addBlockBelow='{{ $blockId }}'; $dispatch('open-modal', { id: 'add-block' })"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </div>
    </div>
</div>
