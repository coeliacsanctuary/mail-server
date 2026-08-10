<div>
    <div class="text-xs uppercase font-semibold mb-2">Text</div>

    <x-newsletter.editable.auto-textarea
        wire:model.live.blur="content"
        placeholder="Type your content..."
        style="min-height: 100px;"
    />
</div>
