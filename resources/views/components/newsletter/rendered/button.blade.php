@props(['href', 'block', 'padding' => null, 'colour' => null, 'textAlign' => null])

<mj-button href="{{ $href }}" @if($padding !== null) padding="{{ $padding }}" @endif @if($colour !== null) background-color="{{ $colour->background() }}" color="{{ $colour->text() }}" @endif @if($textAlign !== null) text-align="{{ $textAlign }}" @endif @if($block === 'single') border-radius="6px" font-size="16px" line-height="115%" inner-padding="8px 25px" css-class="single-button" @endif>
    {{ $slot }}
</mj-button>
