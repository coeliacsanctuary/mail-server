<div>
    <div class="text-xs uppercase font-semibold mb-2">Image</div>

    @if($image)
        <div class="w-full p-2">
            <img src="{{ is_string($image) ? $image : $image->temporaryUrl() }}">
        </div>

        <div class="mt-2 text-base w-full flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="w-6 h-6"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"
                />
            </svg>

            <input type="text" wire:model.live.blur="link" class="flex-1"
                   placeholder="Link (Leave blank for no link)"
            />
        </div>

        <div class="mt-2 text-base w-full flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                 stroke="currentColor" class="w-6 h-6"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"
                />
            </svg>

            <input type="text" wire:model.live.blur="alt" class="flex-1"
                   placeholder="Alt text (shown if the image is blocked, and read by screen readers)"
            />
        </div>
    @endif

    <input type="file" wire:model.live.blur="image" class="text-2xl w-full" accept="image/*"/>
</div>
