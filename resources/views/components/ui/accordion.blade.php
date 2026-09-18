@props(['title', 'open' => false])
<details {{ $attributes->class('ec-surface group') }} @if($open) open @endif>
    <summary class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-4 px-4 font-bold text-slate-900"><span>{{ $title }}</span><span class="transition-transform group-open:rotate-180" aria-hidden="true">⌄</span></summary>
    <div class="border-t border-slate-200 p-4 text-sm leading-6 text-slate-600">{{ $slot }}</div>
</details>
