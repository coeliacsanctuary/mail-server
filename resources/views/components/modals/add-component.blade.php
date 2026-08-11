@php
    /**
     * Each entry's "component" is stored data — it becomes the component name in
     * the campaign's structured_html and is resolved as both a Livewire
     * component path and a Blade view path. Renaming one is a migration of every
     * saved campaign. The titles and icons are display only.
     *
     * Composite icons read as "base thing + button", which is what the three
     * "with button" variants are.
     *
     * @var array<int, array{title: string, component: string, icons: array<int, string>}> $components
     */
    $components = [
        ['title' => 'Title', 'component' => 'title', 'icons' => ['heroicon-o-h1']],
        ['title' => 'Title with Text', 'component' => 'title-with-text', 'icons' => ['heroicon-o-h1', 'heroicon-o-bars-3-bottom-left']],
        ['title' => 'Subtitle', 'component' => 'subtitle', 'icons' => ['heroicon-o-h2']],
        ['title' => 'Button', 'component' => 'button', 'icons' => ['heroicon-o-cursor-arrow-rays']],
        ['title' => 'Text', 'component' => 'text', 'icons' => ['heroicon-o-bars-3-bottom-left']],
        ['title' => 'Text with Button', 'component' => 'text-with-button', 'icons' => ['heroicon-o-bars-3-bottom-left', 'heroicon-o-cursor-arrow-rays']],
        ['title' => 'Horizontal Rule', 'component' => 'hr', 'icons' => ['heroicon-o-minus']],
        ['title' => 'Image', 'component' => 'image', 'icons' => ['heroicon-o-photo']],
        ['title' => 'Image with Button', 'component' => 'image-with-button', 'icons' => ['heroicon-o-photo', 'heroicon-o-cursor-arrow-rays']],
        ['title' => 'Blog', 'component' => 'blog', 'icons' => ['heroicon-o-newspaper']],
        ['title' => 'Recipe', 'component' => 'recipe', 'icons' => ['heroicon-o-cake']],
        ['title' => 'Product', 'component' => 'product', 'icons' => ['heroicon-o-shopping-bag']],
        ['title' => 'Eatery', 'component' => 'eatery', 'icons' => ['heroicon-o-building-storefront']],
    ];
@endphp

<x-mailcoach::modal name="add-component" :title="__mc('Choose a component')" :dismissable="true">
    <div class="grid grid-cols-3 w-full gap-2" x-data>
        @foreach($components as $component)
            <button
                type="button"
                class="editor-picker-tile"
                x-on:click="$dispatch('add-component', ['{{ $component['component'] }}'])"
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
