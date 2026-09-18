@props(['tone' => 'info', 'title' => null])
@php($border = ['info' => 'border-l-blue-700', 'success' => 'border-l-emerald-700', 'warning' => 'border-l-amber-600', 'danger' => 'border-l-red-700'][$tone] ?? 'border-l-blue-700')
<div role="{{ $tone === 'danger' ? 'alert' : 'status' }}" {{ $attributes->class(['ec-alert', $border]) }}>
    <div>@if($title)<p class="font-bold text-slate-900">{{ $title }}</p>@endif<div class="text-sm text-slate-600">{{ $slot }}</div></div>
</div>
