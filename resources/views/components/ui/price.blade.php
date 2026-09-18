@props(['amount', 'prefix' => null, 'original' => null])
<span {{ $attributes->class('inline-flex flex-wrap items-baseline gap-x-2') }}>
    @if($prefix)<span class="text-xs font-semibold text-slate-500">{{ $prefix }}</span>@endif
    <span class="ec-price">Rp {{ number_format((float) $amount, 0, ',', '.') }}</span>
    @if($original !== null && $original > $amount)<del class="text-xs text-slate-500">Rp {{ number_format((float) $original, 0, ',', '.') }}</del>@endif
</span>
