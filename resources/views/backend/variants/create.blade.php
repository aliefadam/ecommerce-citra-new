@extends('layouts.app')

@section('title', 'Create Variant')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Create Variant</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Tambahkan master varian baru (tipe & nilai).</p>
        </div>

        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form action="{{ route('variants.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipe Varian <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Warna, Ukuran, Kapasitas"
                        class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('name') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nilai Varian <span class="text-red-400">*</span></label>
                    <input type="text" name="value" value="{{ old('value') }}" placeholder="Contoh: Merah, XL, 128GB"
                        class="w-full px-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 dark:text-slate-200 placeholder-slate-400 {{ $errors->has('value') ? 'border-2 border-red-400 bg-red-50 dark:bg-red-900/10 dark:border-red-600 focus:ring-red-400' : 'border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:ring-blue-500' }}" />
                    @error('value')
                        <p class="text-xs text-red-500 mt-1.5 flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('variants.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Save Variant</button>
                </div>
            </form>
        </div>
    </main>
@endsection
