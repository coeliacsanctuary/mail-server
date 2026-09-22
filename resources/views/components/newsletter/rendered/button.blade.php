@props(['href', 'block', 'padding' => null])

<mj-button href="{{ $href }}" @if($padding !== null) padding="{{ $padding }}" @endif @if($block === 'single') border-radius="6px" font-size="20px" css-class="mobile-button" @endif>
    {{ $slot }}
</mj-button>
