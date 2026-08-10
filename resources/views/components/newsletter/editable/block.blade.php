@props(['block', 'first', 'last'])

@php
    $components = match($block['block']) {
        'triple' => 3,
        'double' => 2,
        default => 1,
    }
@endphp

{{--
    wire:sort:item is used VERBATIM as a string, not evaluated as JavaScript.
    supportWireSort binds it through Alpine.bind() with a function value, so
    Alpine takes generateEvaluatorFromFunction and simply returns the attribute
    content. Wrapping it in @js() makes the surrounding quotes part of the id,
    and the drop then fails with "No block ['<uuid>']".
--}}
<div wire:key="{{ $block['id'] }}-outer" wire:sort:item="{{ $block['id'] }}">
    <div class="group relative" >
        <div wire:key="{{ $block['id'] }}-inner" class="flex w-full">
            @foreach($block['properties'] as $index => $properties)
                <div
                    wire:key="{{ $block['id'] }}-{{ $index }}-wrapper"
                    class="editable w-full relative"
                    x-data="{ hovered: false }"
                    x-on:mouseenter="hovered = true"
                    x-on:mouseleave="hovered = false"
                >
                    @if($properties['component'])
                        {{--
                            Emptying the column restores the Add Component
                            placeholder, which is also how a component gets
                            swapped for a different one.
                        --}}
                        <div class="absolute" style="top: 0; right: 0; z-index: 20" x-show="hovered" x-cloak>
                            <x-mailcoach::confirm-button
                                class="bg-gray-200 rounded border border-gray-600 p-1 hover:bg-gray-400/10 cursor-pointer"
                                danger
                                :confirm-text="__mc('Remove this component? Its content will be lost.')"
                                :confirm-label="__mc('Remove component')"
                                on-confirm="() => $wire.removeComponent('{{ $block['id'] }}', {{ $index }})"
                                x-tooltip="'{{ __mc('Remove component') }}'"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </x-mailcoach::confirm-button>
                        </div>

                        <livewire:is
                            wire:key="{{ $block['id'] }}-{{ $index }}-component"
                            :component="'newsletter.editable.components.'.$properties['component']['name']"
                            :block-id="$block['id']"
                            :block="$block['block']"
                            :index="$index"
                            :properties="$properties['component']['properties']"
                        />
                    @else
                        <x-newsletter.components.add-component
                            wire:key="{{ $block['id'] }}-{{ $index }}-add"
                            :block-id="$block['id']"
                            :block="$block['block']"
                            :index="$index"
                        />
                    @endif
                </div>
            @endforeach
        </div>

        <x-newsletter.block-actions :block-id="$block['id']" :index="$index" :first="$first" :last="$last" />
    </div>
</div>

