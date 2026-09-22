<div class="component-editor">
    <div class="text-xs uppercase font-semibold mb-2">Text</div>

    <x-newsletter.editable.auto-textarea
        wire:model.live.blur="content"
        placeholder="Type your content..."
        class="editable-body"
        style="min-height: 100px;"
    />
</div>
