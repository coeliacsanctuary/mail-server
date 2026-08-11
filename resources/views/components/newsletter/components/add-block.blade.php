<div class="flex items-center justify-center py-4">
    <button
        type="button"
        class="editor-tile"
        {{--
            Every opener declares its own insertion point. This one appends to
            the end, so it clears the target rather than assuming whatever the
            last opener left behind.
        --}}
        x-on:click="window._addBlockBelow = null; $dispatch('open-modal', { id: 'add-block' })"
    >
        <x-heroicon-o-plus-circle class="w-8 h-8" />

        <span>Add Block</span>
    </button>
</div>
