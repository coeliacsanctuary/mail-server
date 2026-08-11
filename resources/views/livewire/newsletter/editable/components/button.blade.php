<div>
    <div class="text-xs uppercase font-semibold mb-2">Button</div>

    <input type="text" wire:model.live.blur="label" placeholder="Label..." class="text-base w-full"/>

    <div class="mt-2 text-base w-full flex items-center space-x-2">
        <x-heroicon-o-link class="w-6 h-6" />

        <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link"/>
    </div>
</div>
