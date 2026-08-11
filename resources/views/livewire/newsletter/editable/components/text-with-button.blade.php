<div>
    <div class="text-xs uppercase font-semibold mb-2">Text with button</div>

    <x-newsletter.editable.auto-textarea
        wire:model.live.blur="content"
        placeholder="Type your content..."
        style="min-height: 100px;"
    />

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-tag class="w-6 h-6" />

        <input type="text" wire:model.live.blur="label" class="flex-1" placeholder="Label"/>
    </div>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link"/>
    </div>
</div>
