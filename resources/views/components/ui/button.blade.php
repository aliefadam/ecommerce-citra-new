@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'loading' => false])
@php($classes = 'ec-btn ec-btn-' . $variant)
@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" @disabled($attributes->has('disabled') || $loading)
        @if($loading) aria-busy="true" @endif {{ $attributes->class($classes) }}>
        @if($loading)<span class="size-4 animate-spin rounded-full border-2 border-current border-r-transparent" aria-hidden="true"></span>@endif
        {{ $slot }}
    </button>
@endif
