{{-- Shared by Title and Subtitle - see HeadingComponent. --}}
<div>
    <div class="text-xs uppercase font-semibold mb-2">{{ $label }}</div>

    <input type="text" wire:model.live.blur="content" placeholder="{{ $label }}..." class="{{ $inputClass }} w-full"/>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1"
               placeholder="Link (Leave blank for no link)"
        />
    </div>
</div>
