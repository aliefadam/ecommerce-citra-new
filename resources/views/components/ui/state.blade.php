@props(['title', 'description' => null, 'icon' => 'box', 'action' => null])
<div {{ $attributes->class('ec-surface grid place-items-center px-6 py-10 text-center') }}>
    <span class="mb-4 grid size-12 place-items-center rounded-lg bg-slate-100 text-xl text-slate-600" aria-hidden="true"><i class="fi fi-rr-{{ $icon }}"></i></span>
    <h3 class="ec-display text-xl text-slate-900">{{ $title }}</h3>
    @if($description)<p class="mt-2 max-w-md text-sm leading-6 text-slate-600">{{ $description }}</p>@endif
    @if($action)<div class="mt-5">{{ $action }}</div>@endif
</div>
