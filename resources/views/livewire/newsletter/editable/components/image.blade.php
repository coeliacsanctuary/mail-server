<div class="component-editor">
    <div class="text-xs uppercase font-semibold mb-2">Image</div>

    <x-newsletter.editable.image-picker>
        @if($image)
            <img src="{{ is_string($image) ? $image : $image->temporaryUrl() }}" alt="{{ $alt }}" class="image-picker-preview">

            <span class="image-picker-hint">
                <x-heroicon-o-arrow-path class="w-4 h-4" />
                {{ __mc('Replace') }}
            </span>
        @else
            <span class="image-picker-empty">
                <x-heroicon-o-photo class="w-8 h-8" />
                {{ __mc('Choose an image') }}
            </span>
        @endif
    </x-newsletter.editable.image-picker>

    <x-newsletter.editable.component-props stacked>
        <div class="text-base w-full flex items-center space-x-2">
            <x-heroicon-o-link class="w-6 h-6" />

            <input type="text" wire:model.live.blur="link" class="flex-1"
                   placeholder="{{ __mc('Link (Leave blank for no link)') }}"
            />
        </div>

        <div class="text-base w-full flex items-center space-x-2">
            <x-heroicon-o-book-open class="w-6 h-6" />

            <input type="text" wire:model.live.blur="alt" class="flex-1"
                   placeholder="{{ __mc('Alt text (shown if the image is blocked, and read by screen readers)') }}"
            />
        </div>
    </x-newsletter.editable.component-props>
</div>
