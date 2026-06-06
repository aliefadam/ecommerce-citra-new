@extends('layouts.app')

@section('title', 'Edit Banner')

@section('content')
    @php
        $image = (string) $banner->image;
        $imageUrl =
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://') ||
            str_starts_with($image, '//') ||
            str_starts_with($image, 'data:')
                ? $image
                : asset('storage/' . ltrim($image, '/'));
    @endphp
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Edit Banner</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Perbarui data banner homepage.</p>
        </div>

        <form action="{{ route('banners.update', $banner) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipe Banner <span class="text-red-500">*</span></label>
                        <select name="type" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200">
                            <option value="carousel" @selected(old('type', $banner->type) === 'carousel')>Carousel — Slider utama (kiri)</option>
                            <option value="side" @selected(old('type', $banner->type) === 'side')>Side — Banner kanan (maks 2)</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Title (opsional)</label>
                        <input type="text" name="title" value="{{ old('title', $banner->title) }}"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ganti Upload Gambar</label>
                        <div class="flex items-center gap-3">
                            <div id="bannerImagePreviewWrap" class="shrink-0">
                                <img id="bannerImagePreview" src="{{ $imageUrl }}" alt="Preview Banner"
                                    class="w-14 h-14 object-cover rounded-lg border border-slate-200 dark:border-slate-600" />
                            </div>
                            <label
                                class="flex-1 flex items-center gap-2 px-3 py-2.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-500 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors bg-white dark:bg-slate-700/50">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="text-slate-400 shrink-0">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <polyline points="21 15 16 10 5 21" />
                                </svg>
                                <span id="bannerImagePreviewLabel" class="text-xs text-slate-400 truncate">Ganti gambar...</span>
                                <input id="bannerImageFile" type="file" name="image_file" accept="image/*" class="hidden" />
                            </label>
                        </div>
                        <p id="bannerSizeHint" class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">
                            Rekomendasi: <span id="bannerSizeHintText">{{ $banner->type === 'side' ? '800 × 250 px (rasio 16:5, untuk banner kanan)' : '1600 × 700 px (rasio 16:4, untuk slider utama)' }}</span> — otomatis dikonversi saat upload.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Atau URL Gambar</label>
                        <input type="text" name="image_url" value="{{ old('image_url', $banner->image) }}" placeholder="https://..."
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                        @error('image_url')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Target URL (opsional)</label>
                        <input type="text" name="target_url" value="{{ old('target_url', $banner->target_url) }}" placeholder="https://example.com"
                            class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                        @error('target_url')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-5">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Urutan</label>
                            <input type="number" min="1" name="sort_order" value="{{ old('sort_order', $banner->sort_order) }}"
                                class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-200" />
                            @error('sort_order')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="is_active" value="0" />
                            <input type="checkbox" name="is_active" value="1" class="accent-blue-500" @checked(old('is_active', $banner->is_active)) />
                            Active
                        </label>
                        @error('is_active')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit"
                            class="w-full px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">
                            Update Banner
                        </button>
                        <a href="{{ route('banners.index') }}"
                            class="w-full text-center px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </main>
@endsection

@section('script')
    <script>
        (function() {
            const typeSelect = document.querySelector('select[name="type"]');
            const hintText = document.getElementById('bannerSizeHintText');
            const hints = {
                carousel: '1600 × 700 px (rasio 16:4, untuk slider utama)',
                side: '800 × 250 px (rasio 16:5, untuk banner kanan)',
            };
            if (typeSelect && hintText) {
                typeSelect.addEventListener('change', function() {
                    hintText.textContent = hints[this.value] || hints.carousel;
                });
            }

            const input = document.getElementById('bannerImageFile');
            const preview = document.getElementById('bannerImagePreview');
            const label = document.getElementById('bannerImagePreviewLabel');
            if (!input || !preview || !label) return;

            input.addEventListener('change', function(e) {
                const file = e.target.files && e.target.files[0] ? e.target.files[0] : null;
                if (!file) {
                    label.textContent = 'Ganti gambar...';
                    return;
                }
                label.textContent = 'Ganti gambar...';
                const reader = new FileReader();
                reader.onload = function(evt) {
                    preview.src = String(evt.target?.result || '');
                };
                reader.readAsDataURL(file);
            });
        })();
    </script>
@endsection
