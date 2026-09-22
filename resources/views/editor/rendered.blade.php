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
                        <x-newsletter.rendered.column :block="$block['block']" :position="$index">
                            @foreach($properties['components'] ?? [] as $blockComponent)
                                @if(\Illuminate\Support\Facades\View::exists("components.newsletter.rendered.components.{$blockComponent['name']}"))
                                    <x-dynamic-component
                                        component="newsletter.rendered.components.{{ $blockComponent['name'] }}"
                                        :properties="$blockComponent['properties']"
                                        :block="$block['block']"
                                    />
                                @endif
                            @endforeach
                        </x-newsletter.rendered.column>
                    @endforeach
                </mj-section>
            </mj-wrapper>
        @endforeach

        <x-newsletter.rendered.footer />
    </mj-body>
</mjml>
