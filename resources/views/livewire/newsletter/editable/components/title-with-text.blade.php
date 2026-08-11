<div>
    <div class="text-xs uppercase font-semibold mb-2">Title with text</div>

    <input type="text" wire:model.live.blur="title" placeholder="Title..." class="text-2xl w-full"/>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link (Leave blank for no link)"/>
    </div>

    <x-newsletter.editable.auto-textarea
        wire:model.live.blur="content"
        placeholder="Type your content..."
        class="mt-2"
        style="min-height: 100px;"
    />
</div>
