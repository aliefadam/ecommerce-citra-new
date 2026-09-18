@props(['tone' => 'neutral'])
<span {{ $attributes->class(['ec-badge', 'ec-badge-' . $tone]) }}>{{ $slot }}</span>
