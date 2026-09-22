@php
    $components = [
        ['title' => 'Title', 'components' => ['title'], 'icons' => ['heroicon-o-h1']],
        ['title' => 'Title with Text', 'components' => ['title', 'text'], 'icons' => ['heroicon-o-h1', 'heroicon-o-bars-3-bottom-left']],
        ['title' => 'Subtitle', 'components' => ['subtitle'], 'icons' => ['heroicon-o-h2']],
        ['title' => 'Button', 'components' => ['button'], 'icons' => ['heroicon-o-cursor-arrow-rays']],
        ['title' => 'Text', 'components' => ['text'], 'icons' => ['heroicon-o-bars-3-bottom-left']],
        ['title' => 'Text with Button', 'components' => ['text', 'button'], 'icons' => ['heroicon-o-bars-3-bottom-left', 'heroicon-o-cursor-arrow-rays']],
        ['title' => 'Horizontal Rule', 'components' => ['hr'], 'icons' => ['heroicon-o-minus']],
        ['title' => 'Image', 'components' => ['image'], 'icons' => ['heroicon-o-photo']],
        ['title' => 'Image with Button', 'components' => ['image', 'button'], 'icons' => ['heroicon-o-photo', 'heroicon-o-cursor-arrow-rays']],
        ['title' => 'Blog', 'components' => ['blog'], 'icons' => ['heroicon-o-newspaper']],
        ['title' => 'Recipe', 'components' => ['recipe'], 'icons' => ['heroicon-o-cake']],
        ['title' => 'Product', 'components' => ['product'], 'icons' => ['heroicon-o-shopping-bag']],
        ['title' => 'Eatery', 'components' => ['eatery'], 'icons' => ['heroicon-o-building-storefront']],
    ];
@endphp

<x-mailcoach::modal name="add-component" :title="__mc('Choose a component')" :dismissable="true">
    <div class="grid grid-cols-3 w-full gap-2" x-data>
        @foreach($components as $component)
            <button
                type="button"
                class="editor-picker-tile"
                x-on:click="$dispatch('add-component', [{{ Illuminate\Support\Js::from($component['components']) }}])"
            >
                <span class="flex items-center gap-1">
                    @foreach($component['icons'] as $icon)
                        <x-icon :name="$icon" class="w-6 h-6" />
                    @endforeach
                </span>

                <span class="text-sm">{{ $component['title'] }}</span>
            </button>
        @endforeach
    </div>
</x-mailcoach::modal>
