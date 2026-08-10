{{-- Shared by Blog, Recipe and Product - see SearchableApiComponent. --}}
<div class="flex relative">
    @if($selectedId)
        <div class="flex @unless($block === 'single') flex-col space-y-2 items-center justify-center @endunless">
            <div style="@if($block === 'single') width: 20% @else width: 90% @endif">
                <img src="{{ $selected->main_image }}"/>
            </div>
            <div style="@if($block === 'single') width: 80% @else width: 90% @endif">
                <h2 class="text-xl">{{ $selected->title }}</h2>
                <p class="text-italic text-xs">{{ $meta }}</p>

                <x-newsletter.editable.auto-textarea
                    wire:model.live="description"
                    class="border m-1 p-1"
                />

                <a class="text-sm cursor-pointer" wire:click="remove">Remove...</a>
            </div>
        </div>
    @else
        <input type="text" wire:model.live.debounce="search" placeholder="Search {{ $label }}..." class="text-2xl w-full"/>
    @endif

    @if($search !== '')
        <div class="absolute bg-white min-h-[8rem] mt-2 shadow text-base w-full z-50"
             style="top: 100%; max-height: 500px; overflow: scroll;"
        >
            <ul>
                @forelse($results as $result)
                    <li class="flex p-2 space-x-2 hover:bg-gray-200 transition cursor-pointer @unless($loop->last) border-b @endunless"
                        wire:click="select({{ $result->id }})">
                        <div style="width: 20%">
                            <img src="{{ $result->main_image }}"/>
                        </div>
                        <div style="width: 80%">
                            <h2 class="text-xl">{{ $result->title }}</h2>
                            <p>{{ $result->meta_description }}</p>
                            <p class="text-italic text-xs">{{ $result->created_at }}</p>
                        </div>
                    </li>
                @empty
                    <li>No {{ $label }} found...</li>
                @endforelse
            </ul>
        </div>
    @endif
</div>
