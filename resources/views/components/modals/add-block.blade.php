@php
    $layouts = [
        ['title' => 'Single Column', 'block' => 'single', 'columns' => 1],
        ['title' => 'Double Column', 'block' => 'double', 'columns' => 2],
        ['title' => 'Triple Column', 'block' => 'triple', 'columns' => 3],
    ];
@endphp

<x-mailcoach::modal name="add-block" :title="__mc('Add a block')" :dismissable="true">
    <div class="grid grid-cols-3 w-full gap-2" x-data>
        @foreach($layouts as $layout)
            <button
                type="button"
                class="editor-picker-tile"
                x-on:click="$dispatch('add-block', ['{{ $layout['block'] }}', window._addBlockBelow]); $dispatch('close-modal', {id:'add-block'})"
            >
                <span class="flex">
                    {{--
                        One plain square per column, so the glyph shows the
                        count. The old markup drew these by hand and sized the
                        single-column one with w-12 against w-8 for the other
                        two — a class that does not exist in the compiled CSS,
                        so it rendered at its intrinsic size instead.
                    --}}
                    @for($column = 0; $column < $layout['columns']; $column++)
                        <x-heroicon-o-stop class="w-8 h-8" />
                    @endfor
                </span>

                <span>{{ $layout['title'] }}</span>
            </button>
        @endforeach
    </div>
</x-mailcoach::modal>
