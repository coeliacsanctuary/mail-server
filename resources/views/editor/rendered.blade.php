<mjml>
    <x-newsletter.rendered.metas />

    <mj-body background-color="#f7f7f7">
        <x-newsletter.rendered.header />

        @foreach($blocks as $block)
            <mj-wrapper>
                <mj-section>
                    @foreach($block['properties'] as $index => $properties)
                        <mj-column>
                            @if(\Illuminate\Support\Facades\View::exists("components.newsletter.rendered.components.{$properties['component']['name']}"))
                                <x-dynamic-component
                                    component="newsletter.rendered.components.{{ $properties['component']['name'] }}"
                                    :properties="$properties['component']['properties']"
                                    :block="$block['block']"
                                    :position="$index"
                                />
                           @endif
                        </mj-column>
                    @endforeach
                </mj-section>
            </mj-wrapper>
        @endforeach

        <x-newsletter.rendered.footer />
    </mj-body>
</mjml>
