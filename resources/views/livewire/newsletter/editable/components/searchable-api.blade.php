{{-- Shared by Blog, Recipe and Product - see SearchableApiComponent. --}}
<div>
    <div class="text-xs uppercase font-semibold mb-2">{{ $heading }}</div>

    <div class="relative">
        @if($selectedId)
            <div class="flex @unless($block === 'single') flex-col gap-2 items-center justify-center @endunless">
                <div style="@if($block === 'single') width: 20% @else width: 90% @endif">
                    <img src="{{ $selected->main_image }}" alt=""/>
                </div>
                <div style="@if($block === 'single') width: 80% @else width: 90% @endif">
                    <h2 class="text-xl">{{ $selected->title }}</h2>
                    <p class="italic text-xs">{{ $meta }}</p>

                    <x-newsletter.editable.auto-textarea
                        wire:model.live="description"
                        class="border p-1 mt-2"
                    />

                    {{--
                        Only clears the selection - the component stays. The X in
                        the corner of the column is the one that removes the
                        component itself, and "Remove..." read like both.
                    --}}
                    <button type="button" class="text-sm cursor-pointer underline" wire:click="remove">
                        Choose a different {{ strtolower($heading) }}
                    </button>
                </div>
            </div>
        @else
            <div class="flex items-center gap-2">
                <input
                    type="text"
                    wire:model.live.debounce="search"
                    placeholder="Search {{ $label }}..."
                    class="text-2xl w-full"
                />

                {{--
                    Searching reaches out to coeliacsanctuary.co.uk, so without
                    this the box sits there looking broken. Targets the property
                    rather than an action because the search is a wire:model
                    update.
                --}}
                <span wire:loading wire:target="search" class="shrink-0">
                    <x-heroicon-o-arrow-path class="w-5 h-5 animate-spin" />
                    <span class="visually-hidden">Searching...</span>
                </span>
            </div>
        @endif

        @if($search !== '')
            <div class="editor-search-results">
                <ul>
                    @forelse($results as $result)
                        <li class="editor-search-result" wire:click="select({{ $result->id }})">
                            <div style="width: 20%">
                                <img src="{{ $result->main_image }}" alt=""/>
                            </div>
                            <div style="width: 80%">
                                <h2 class="text-xl">{{ $result->title }}</h2>
                                <p>{{ $result->meta_description }}</p>
                                <p class="italic text-xs">{{ $result->created_at }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="editor-search-empty">No {{ $label }} found...</li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
</div>
