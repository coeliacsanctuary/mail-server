@props(['field' => 'image'])

<div
    x-data="{ uploading: false, progress: 0 }"
    x-on:livewire-upload-start="uploading = true; progress = 0"
    x-on:livewire-upload-finish="uploading = false"
    x-on:livewire-upload-cancel="uploading = false"
    x-on:livewire-upload-error="uploading = false"
    x-on:livewire-upload-progress="progress = $event.detail.progress"
>
    <input type="file" wire:model="{{ $field }}" class="text-2xl w-full" accept="image/*"/>

    <div x-show="uploading" x-cloak class="mt-2 flex items-center gap-2">
        <progress max="100" x-bind:value="progress" class="w-full"></progress>
        <span class="text-xs shrink-0" x-text="progress + '%'"></span>
    </div>
</div>
