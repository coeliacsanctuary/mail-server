@props(['field' => 'image'])

{{--
    The file picker, with progress. Uploads go to S3, so a phone photo can take
    several seconds during which the editor otherwise looks like it has hung.

    The livewire-upload-* events are dispatched on the <input> and bubble to this
    wrapper. Plain wire:model rather than wire:model.live.blur: modifiers mean
    nothing to a file input, which always uploads on change.
--}}
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
