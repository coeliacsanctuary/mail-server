<div class="form-grid">
{{--    <x-mailcoach::template-chooser :clearable="false" />--}}

    <div class="relative w-full">
        <div class="newsletter">
            <div class="newsletter-inner">
                <x-newsletter.editable.header />

                <div>
                    @foreach($blocks as $block)
                        <x-newsletter.editable.block
                            :block="$block"
                            :first="$loop->first"
                            :last="$loop->last"
                        />
                    @endforeach

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
