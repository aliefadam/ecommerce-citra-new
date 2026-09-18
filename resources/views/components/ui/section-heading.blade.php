@props(['eyebrow' => null, 'title', 'description' => null])
<div {{ $attributes->class('flex flex-wrap items-end justify-between gap-4 border-b border-slate-200 pb-4') }}>
    <div>@if($eyebrow)<p class="mb-1 text-xs font-extrabold uppercase tracking-[.16em] text-blue-700">{{ $eyebrow }}</p>@endif
        <h2 class="ec-display text-2xl text-slate-950 sm:text-3xl">{{ $title }}</h2>
        @if($description)<p class="mt-1 max-w-2xl text-sm text-slate-600">{{ $description }}</p>@endif
    </div>
    @isset($action)<div>{{ $action }}</div>@endisset
</div>
