@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    @php
        $toneMap = [
            'blue' => ['box' => 'bg-blue-50 text-blue-600', 'border' => 'hover:border-blue-200'],
            'cyan' => ['box' => 'bg-cyan-50 text-cyan-600', 'border' => 'hover:border-cyan-200'],
            'emerald' => ['box' => 'bg-emerald-50 text-emerald-600', 'border' => 'hover:border-emerald-200'],
            'amber' => ['box' => 'bg-amber-50 text-amber-600', 'border' => 'hover:border-amber-200'],
            'orange' => ['box' => 'bg-orange-50 text-orange-600', 'border' => 'hover:border-orange-200'],
            'rose' => ['box' => 'bg-rose-50 text-rose-600', 'border' => 'hover:border-rose-200'],
            'violet' => ['box' => 'bg-violet-50 text-violet-600', 'border' => 'hover:border-violet-200'],
        ];
    @endphp

    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <p class="text-xs font-bold uppercase tracking-[0.22em] text-blue-600">Report Center</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-slate-800 dark:text-white">Reports</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Pilih laporan yang ingin dibuka. Setiap laporan punya halaman detail dan filter sendiri.</p>
        </div>

        <div class="space-y-4">
            @foreach ($groups as $group)
                @php $groupTone = $toneMap[$group['tone']] ?? $toneMap['blue']; @endphp
                <section class="rounded-2xl border border-sky-100 bg-white p-4 sm:p-5 shadow-sm dark:bg-slate-800 dark:border-slate-700">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl {{ $groupTone['box'] }}">
                            <i data-lucide="{{ $group['icon'] }}" class="h-5 w-5"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-800 dark:text-white">{{ $group['title'] }}</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $group['description'] }}</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group['items'] as $item)
                            @php $tone = $toneMap[$item['tone']] ?? $toneMap['blue']; @endphp
                            <a href="{{ $item['route'] }}"
                                class="group flex min-h-[96px] items-center gap-3 rounded-xl border border-sky-100 bg-white p-3 transition-all hover:-translate-y-0.5 hover:shadow-md {{ $tone['border'] }} dark:bg-slate-900/30 dark:border-slate-700">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tone['box'] }}">
                                    <i data-lucide="{{ $item['icon'] }}" class="h-5 w-5"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-extrabold text-slate-800 dark:text-white">{{ $item['title'] }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $item['description'] }}</p>
                                </div>
                                <i data-lucide="arrow-right" class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-blue-600"></i>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </main>
@endsection
