<mjml>
    <x-newsletter.rendered.metas />

    <mj-body background-color="#f7f7f7">
        @if(filled($preheader))
            <mj-raw>
                <div id="preheader" style="display:none;font-size:1px;color:#ffffff;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">{{ $preheader }}</div>
            </mj-raw>
        @endif

        <x-newsletter.rendered.header />

        @foreach($blocks as $block)
            <mj-wrapper>
                <mj-section>
                    @foreach($block['properties'] as $index => $properties)
                        <mj-column>
                            @php
                                $component = data_get($properties, 'component.name')
                            @endphp
                            @if($component && \Illuminate\Support\Facades\View::exists("components.newsletter.rendered.components.{$component}"))
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
