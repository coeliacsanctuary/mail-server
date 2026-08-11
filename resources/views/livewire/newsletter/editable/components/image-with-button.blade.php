<div>
    <div class="text-xs uppercase font-semibold mb-2">Image with button</div>

    @if($image)
        <div class="w-full p-2">
            <img src="{{ is_string($image) ? $image : $image->temporaryUrl() }}" alt="{{ $alt }}">

            <div class="mt-2 text-base w-full flex items-center space-x-2">
                <x-heroicon-o-tag class="w-6 h-6" />

                <input type="text" wire:model.live.blur="label" class="flex-1" placeholder="Label (Leave blank to hide button)"/>
            </div>

            <div class="mt-2 text-base w-full flex items-center space-x-2">
                <x-heroicon-o-link class="w-6 h-6" />

                <input type="text" wire:model.live.blur="link" class="flex-1" placeholder="Link"/>
            </div>

            <div class="mt-2 text-base w-full flex items-center space-x-2">
                <x-heroicon-o-book-open class="w-6 h-6" />

                <input type="text" wire:model.live.blur="alt" class="flex-1"
                       placeholder="Alt text (shown if the image is blocked, and read by screen readers)"
                />
            </div>
        </div>
    @endif

    <x-newsletter.editable.image-upload />
</div>
