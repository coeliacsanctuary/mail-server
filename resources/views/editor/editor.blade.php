<div class="form-grid">

    <x-mailcoach::text-field
        name="preheader"
        wire:model.live.blur="preheader"
        :label="__mc('Preview text')"
        :placeholder="__mc('Shown next to the subject line in the inbox')"
        :description="__mc('The snippet inboxes show after the subject. Leave blank and most clients will show the top of the email instead.')"
    />

    <div class="relative w-full">
        <div class="newsletter">
            <div class="newsletter-inner">
                <x-newsletter.editable.header />

                <div>
                    <div
                        wire:sort.ghost="reorderBlock"
                        wire:sort:config="{ handle: '[wire\\:sort\\:handle]' }"
                    >
                        @foreach($blocks as $block)
                            <x-newsletter.editable.block
                                :block="$block"
                                :first="$loop->first"
                                :last="$loop->last"
                            />
                        @endforeach
                    </div>

                    <x-newsletter.components.add-block />
                </div>

                <x-newsletter.editable.footer />
            </div>
        </div>
    </div>
</div>

@push('modals')
    <x-modals.add-block />

    <x-modals.add-component />
@endpush
