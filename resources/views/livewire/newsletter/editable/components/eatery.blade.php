<div>
    <div class="text-xs uppercase font-semibold mb-2">Eatery</div>

    <div class="flex relative">
        <div class="flex @unless($block === 'single') flex-col gap-2 items-center justify-center @endunless">
            <div style="@if($block === 'single') width: 80% @else width: 90% @endif">
                {{-- Every press fetches a fresh random eatery from the API. --}}
                <button type="button" class="editor-inline-button" wire:click="randomEatery">
                    <span wire:loading.remove wire:target="randomEatery">Randomise</span>
                    <span wire:loading wire:target="randomEatery" class="flex items-center gap-2">
                        <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                        Randomising...
                    </span>
                </button>

                <h2 class="text-xl">{{ $eatery->title }}</h2>
                <p class="text-lg">{{ $eatery->description }}</p>
            </div>
        </div>
    </div>
</div>
