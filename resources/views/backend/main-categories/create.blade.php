@extends('layouts.app')

@section('title', 'Create Main Category')

@section('content')
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form id="mainCategoryForm" action="{{ route('main-categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Category Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Upload Gambar Icon</label>
                    <div class="flex items-center gap-3">
                        <div id="mainCategoryImagePreviewWrap" class="hidden flex-shrink-0">
                            <img id="mainCategoryImagePreview" src="" alt="Preview Main Category"
                                class="w-14 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
                        </div>
                        <label
                            class="flex-1 flex items-center gap-2 px-3 py-2.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-500 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-white dark:bg-slate-700/50">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="text-slate-400 flex-shrink-0">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                            <span id="mainCategoryImagePreviewLabel" class="text-xs text-slate-400 truncate">Pilih gambar...</span>
                            <input id="mainCategoryImageFile" type="file" name="image_file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="hidden" />
                        </label>
                    </div>
                    @error('image_file')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Atau URL Gambar</label>
                    <input type="text" name="image_url" value="{{ old('image_url') }}" placeholder="https://..."
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('image_url')
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
                        <p class="mt-1 text-xs leading-5 text-blue-700 dark:text-blue-300">Setelah kategori disimpan, Anda langsung diarahkan untuk memilih field spesifikasi yang diperlukan.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('main-categories.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Simpan & Atur Spesifikasi</button>
                </div>
            </form>
        </div>
    </main>
@endsection

@section('script')
    @include('backend.main-categories.partials.image-upload-script', ['emptyLabel' => 'Pilih gambar...'])
@endsection
