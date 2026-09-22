@props(['block', 'first', 'last'])

<div class="newsletter-block" wire:key="{{ $block['id'] }}-outer" wire:sort:item="{{ $block['id'] }}">
    <div class="group relative">
        <div wire:key="{{ $block['id'] }}-inner" class="flex w-full">
            @foreach($block['properties'] as $index => $properties)
                <div
                    wire:key="{{ $block['id'] }}-{{ $index }}-wrapper"
                    class="editable w-full relative"
                >
                    <div wire:sort.ghost="reorderComponent">
                        @foreach($properties['components'] ?? [] as $blockComponent)
                            <div
                                wire:key="{{ $blockComponent['id'] }}-wrapper"
                                wire:sort:item="{{ $blockComponent['id'] }}"
                                class="relative"
                                x-data="{ hovered: false }"
                                x-on:mouseenter="hovered = true"
                                x-on:mouseleave="hovered = false"
                            >
                                <div class="component-remove" x-show="hovered" x-cloak>
                                    <x-mailcoach::confirm-button
                                        danger
                                        :confirm-text="__mc('Remove this component? Its content will be lost.')"
                                        :confirm-label="__mc('Remove component')"
                                        on-confirm="() => $wire.removeComponent('{{ $blockComponent['id'] }}')"
                                        x-tooltip="'{{ __mc('Remove component') }}'"
                                    >
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                        <span class="visually-hidden">{{ __mc('Remove component') }}</span>
                                    </x-mailcoach::confirm-button>
                                </div>

                                <livewire:is
                                    wire:key="{{ $blockComponent['id'] }}-component"
                                    :component="'newsletter.editable.components.'.$blockComponent['name']"
                                    :block-id="$block['id']"
                                    :block="$block['block']"
                                    :component-id="$blockComponent['id']"
                                    :properties="$blockComponent['properties']"
                                />
                            </div>
                        @endforeach
                    </div>

                    <x-newsletter.components.add-component
                        wire:key="{{ $block['id'] }}-{{ $index }}-add"
                        :block-id="$block['id']"
                        :block="$block['block']"
                        :index="$index"
                        :filled="filled($properties['components'] ?? [])"
                    />
                </div>
            @endforeach
        </div>

        <x-newsletter.block-actions :block-id="$block['id']" :first="$first" :last="$last" />
    </div>
</div>
