{{-- x-tab: Individual tab button, must be inside x-tabs --}}
@props(['value' => null])

<button type="button"
    class="x-tabs-btn"
    data-tab="{{ $value }}"
    @mouseenter="place($el)"
    @click="active === '{{ $value }}' ? ($event.stopImmediatePropagation(), $event.preventDefault()) : select('{{ $value }}', $el)"
    {{ $attributes->whereStartsWith(['wire:', 'x-on:', '@', ':']) }}
>{{ $slot }}</button>
