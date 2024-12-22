<div class="flex relative">
    <input type="hidden" wire.model="eateryId"/>
    <div class="flex @unless($block === 'single') flex-col space-y-2 items-center justify-center @endunless">
        <div style="@if($block === 'single') width: 80% @else width: 90% @endif">
            <a class="bg-gray-200 p-3 inline-block text-base cursor-pointer" wire:click.prevent="randomEatery()">
                Randomise
            </a>

            <h2 class="text-xl">{{ $eatery->title }}</h2>
            <p class="text-lg">{{ $eatery->description }}</p>
        </div>
    </div>
</div>
