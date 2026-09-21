@extends('layouts.app')

@section('title', 'Template Spesifikasi')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[.18em] text-blue-600">Master katalog teknis</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">Template Spesifikasi</h1>
            <p class="mt-1 text-sm text-slate-500">Atur field yang muncul untuk setiap jenis produk.</p>
        </div>
        <a href="{{ route('specification-templates.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Buat Template</a>
    </div>

    @if (session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($templates as $template)
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-800">
                <div class="absolute inset-y-0 left-0 w-1 {{ $template->is_active ? 'bg-blue-600' : 'bg-slate-300' }}"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $template->code }}</p>
                        <h2 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $template->name }}</h2>
                    </div>
                    <span class="rounded-full px-2.5 py-1 text-[11px] font-bold {{ $template->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $template->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <p class="mt-3 min-h-10 text-sm leading-5 text-slate-500">{{ $template->description ?: 'Belum ada deskripsi.' }}</p>
                <dl class="mt-5 grid grid-cols-3 divide-x divide-slate-200 rounded-xl bg-slate-50 py-3 text-center dark:divide-slate-700 dark:bg-slate-900/40">
                    <div><dt class="text-[10px] uppercase text-slate-400">Field</dt><dd class="mt-1 font-bold text-slate-800 dark:text-white">{{ $template->fields_count }}</dd></div>
                    <div><dt class="text-[10px] uppercase text-slate-400">Kategori</dt><dd class="mt-1 font-bold text-slate-800 dark:text-white">{{ $template->category_details_count }}</dd></div>
                    <div><dt class="text-[10px] uppercase text-slate-400">Default</dt><dd class="mt-1 font-bold text-slate-800 dark:text-white">{{ $template->default_main_categories_count }}</dd></div>
                </dl>
                <a href="{{ route('specification-templates.edit', $template) }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-600 dark:text-slate-200">Konfigurasi Template</a>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-500">Belum ada template spesifikasi.</div>
        @endforelse
    </div>
</main>
@endsection
