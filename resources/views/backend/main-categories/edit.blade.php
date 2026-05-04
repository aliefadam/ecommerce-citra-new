@extends('layouts.app')

@section('title', 'Edit Main Category')

@section('content')
    @php
        $image = (string) ($mainCategory->image ?? '');
        $imageUrl =
            $image !== '' &&
            (str_starts_with($image, 'http://') ||
                str_starts_with($image, 'https://') ||
                str_starts_with($image, '//') ||
                str_starts_with($image, 'data:'))
                ? $image
                : ($image !== '' ? asset('storage/' . ltrim($image, '/')) : '');
    @endphp
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-3xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form action="{{ route('main-categories.update', $mainCategory) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Name</label>
                    <input type="text" name="name" value="{{ old('name', $mainCategory->name) }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Upload Gambar Icon</label>
                    <div class="flex items-center gap-3">
                        <div id="mainCategoryImagePreviewWrap" class="{{ $imageUrl ? '' : 'hidden' }} flex-shrink-0">
                            <img id="mainCategoryImagePreview" src="{{ $imageUrl }}" alt="Preview Main Category"
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
                            <span id="mainCategoryImagePreviewLabel" class="text-xs text-slate-400 truncate">{{ $imageUrl ? 'Ganti gambar...' : 'Pilih gambar...' }}</span>
                            <input id="mainCategoryImageFile" type="file" name="image_file" accept="image/*" class="hidden" />
                        </label>
                    </div>
                    @error('image_file')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Atau URL Gambar</label>
                    <input type="text" name="image_url" value="{{ old('image_url', $mainCategory->image) }}" placeholder="https://..."
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('image_url')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('main-categories.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Update</button>
                </div>
            </form>
        </div>
    </main>
@endsection

@section('script')
    <script>
        (function() {
            const input = document.getElementById('mainCategoryImageFile');
            const preview = document.getElementById('mainCategoryImagePreview');
            const wrap = document.getElementById('mainCategoryImagePreviewWrap');
            const label = document.getElementById('mainCategoryImagePreviewLabel');
            if (!input || !preview || !wrap || !label) return;
            input.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                if (!file) {
                    label.textContent = 'Ganti gambar...';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    preview.src = String(evt.target?.result || '');
                    wrap.classList.remove('hidden');
                    label.textContent = 'Ganti gambar...';
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endsection
