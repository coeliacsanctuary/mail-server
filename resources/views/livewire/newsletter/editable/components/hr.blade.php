<div class="component-editor">
    <div class="text-xs uppercase font-semibold mb-2">Horizontal rule</div>

    <div style="border-top: 2px solid {{ $this->brandColour()->background() }}; margin: 10px 0; height:1px;"></div>

    <x-newsletter.editable.component-props>
        <x-newsletter.editable.colour-swatches :component-id="$componentId" :selected="$colour" />
    </x-newsletter.editable.component-props>
</div>
