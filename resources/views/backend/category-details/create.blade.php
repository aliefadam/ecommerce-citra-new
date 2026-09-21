@extends('layouts.app')

@section('title', 'Create Category Detail')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form action="{{ route('category-details.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Main Category</label>
                    <select name="main_category_id"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih kategori utama</option>
                        @foreach ($mainCategories as $mainCategory)
                            <option value="{{ $mainCategory->id }}" @selected((string) old('main_category_id') === (string) $mainCategory->id)>{{ $mainCategory->name }}</option>
                        @endforeach
                    </select>
                    @error('main_category_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Detail Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-950/40">
                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-blue-600 text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4Z" />
                        </svg>
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-blue-900 dark:text-blue-200">Template spesifikasi dibuat otomatis</p>
                        <p class="mt-1 text-xs leading-5 text-blue-700 dark:text-blue-300">Setelah kategori disimpan, Anda langsung diarahkan untuk memilih field yang tampil pada produk.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('category-details.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Simpan & Atur Spesifikasi</button>
                </div>
            </form>
        </div>
    </main>
@endsection
