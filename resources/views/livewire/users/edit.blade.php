<form
    class="card-grid"
    wire:submit="save"
    @keydown.prevent.window.cmd.s="$wire.call('save')"
    @keydown.prevent.window.ctrl.s="$wire.call('save')"
    method="POST"
>
    @csrf
    @method('PUT')

    <x-mailcoach::card>
        <x-mailcoach::text-field type="email" :label="__mc('Email')" name="email" wire:model.live="email" required />

        <x-mailcoach::text-field :label="__mc('Name')" name="name" wire:model.live="name" required />

        <x-mailcoach::form-buttons>
            <x-mailcoach::button :label="__mc('Save user')" />
        </x-mailcoach::form-buttons>
    </x-mailcoach::card>
</form>
