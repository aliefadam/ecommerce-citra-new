@extends('layouts.app')

@section('title', 'Buat Template Spesifikasi')

@section('content')
<main class="flex-1 p-4 sm:p-6 mt-6">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('specification-templates.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">&larr; Template spesifikasi</a>
        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-800">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Buat template baru</h1>
            <p class="mt-1 text-sm text-slate-500">Setelah disimpan, Anda dapat memilih field dan mengatur pilihan nilainya.</p>
            <form action="{{ route('specification-templates.store') }}" method="POST" class="mt-6 space-y-5">
                @csrf
                <div><label class="mb-1.5 block text-sm font-bold text-slate-700">Nama</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Gasket"></div>
                <div><label class="mb-1.5 block text-sm font-bold text-slate-700">Kode stabil</label><input name="code" value="{{ old('code') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 font-mono text-sm focus:ring-2 focus:ring-blue-500" placeholder="Opsional, contoh: gasket"><p class="mt-1 text-xs text-slate-400">Huruf, angka, dash, atau underscore. Kosongkan agar dibuat otomatis.</p></div>
                <div><label class="mb-1.5 block text-sm font-bold text-slate-700">Deskripsi</label><textarea name="description" rows="3" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea></div>
                @if ($errors->any())<div class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
                <div class="flex justify-end gap-3"><a href="{{ route('specification-templates.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600">Batal</a><button class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-blue-700">Buat & Konfigurasi</button></div>
            </form>
        </div>
    </div>
</main>
@endsection
