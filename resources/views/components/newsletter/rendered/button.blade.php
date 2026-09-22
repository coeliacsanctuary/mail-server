@props(['href', 'block', 'padding' => null])

<mj-button href="{{ $href }}" @if($padding !== null) padding="{{ $padding }}" @endif @if($block === 'single') border-radius="6px" font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" @endif>
    {{ $slot }}
</mj-button>
