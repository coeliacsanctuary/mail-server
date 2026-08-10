{{--
    A textarea that grows to fit its content. Callers pass wire:model,
    placeholder and any extra classes through the attribute bag.
--}}
<textarea
    {{ $attributes->class(['w-full', 'text-base']) }}
    x-data="{ resize: () => { $el.style.height = '5px'; $el.style.height = $el.scrollHeight + 'px' } }"
    x-init="resize()"
    x-on:input="resize()"
></textarea>
