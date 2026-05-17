@php
    $selectedType = old('type', $contentPage->type ?? 'page');
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipe Konten</label>
            <select name="type" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" required>
                <option value="page" @selected($selectedType === 'page')>Halaman Statis</option>
                <option value="post" @selected($selectedType === 'post')>Blog / Artikel</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul</label>
            <input type="text" name="title" value="{{ old('title', $contentPage->title ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" required>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $contentPage->slug ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" placeholder="tentang-kami">
            <p class="mt-1 text-xs text-slate-400">Kosongkan untuk membuat slug otomatis dari judul.</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ringkasan</label>
            <textarea name="excerpt" rows="3" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" placeholder="Ringkasan singkat untuk daftar blog atau hero halaman">{{ old('excerpt', $contentPage->excerpt ?? '') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Konten</label>
            <textarea name="content" rows="14" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 font-mono" placeholder="<p>Tulis konten halaman di sini...</p>">{{ old('content', $contentPage->content ?? '') }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Bisa isi teks biasa atau HTML sederhana seperti &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, dan &lt;strong&gt;.</p>
        </div>
    </div>

    <div class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hero Image URL</label>
                <input type="url" name="hero_image_url" value="{{ old('hero_image_url', $contentPage->hero_image ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Upload Hero Image</label>
                <input type="file" name="hero_image_file" accept="image/*" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            @if (!empty($contentPage->hero_image))
                <img src="{{ $contentPage->hero_image }}" alt="{{ $contentPage->title }}" class="w-full rounded-xl border border-slate-200 object-cover">
            @endif
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $contentPage->meta_title ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Meta Description</label>
                <textarea name="meta_description" rows="3" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">{{ old('meta_description', $contentPage->meta_description ?? '') }}</textarea>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Publish</label>
                <input type="datetime-local" name="published_at" value="{{ old('published_at', isset($contentPage) && $contentPage->published_at ? $contentPage->published_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="hidden" name="is_active" value="0" />
                <input type="checkbox" name="is_active" value="1" class="accent-blue-500" @checked(old('is_active', $contentPage->is_active ?? true)) />
                Active / Publish
            </label>
        </div>

        <div class="flex flex-col gap-2">
            <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Konten</button>
            <a href="{{ route('content-pages.index') }}" class="w-full text-center px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
        </div>
    </div>
</div>
