@props(['block', 'first', 'last'])

@php
    $components = match($block['block']) {
        'triple' => 3,
        'double' => 2,
        default => 1,
    }
@endphp

<div class="group relative" wire:key="{{ $block['id'] }}-outer">
    <div wire:key="{{ $block['id'] }}-inner" class="flex w-full">
        @foreach($block['properties'] as $index => $properties)
            <div wire:key="{{ $block['id'] }}-{{ $index }}-wrapper" class="editable w-full">
                @if($properties['component'])
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
