<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Judul Promo</label>
            <input type="text" name="title" value="{{ old('title', $promoPage->title ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" required>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $promoPage->slug ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" placeholder="promo-akhir-bulan">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Subtitle</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $promoPage->subtitle ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Deskripsi</label>
            <textarea name="description" rows="8" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">{{ old('description', $promoPage->description ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Hero Image URL</label>
                <input type="url" name="hero_image_url" value="{{ old('hero_image_url', $promoPage->hero_image ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Upload Hero Image</label>
                <input type="file" name="hero_image_file" accept="image/*" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">CTA Label</label>
                <input type="text" name="cta_label" value="{{ old('cta_label', $promoPage->cta_label ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">CTA URL</label>
                <input type="url" name="cta_url" value="{{ old('cta_url', $promoPage->cta_url ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
        </div>
    </div>
    <div class="space-y-5">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Mulai</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($promoPage) && $promoPage->starts_at ? $promoPage->starts_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Berakhir</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($promoPage) && $promoPage->ends_at ? $promoPage->ends_at->format('Y-m-d\TH:i') : '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input type="hidden" name="is_active" value="0" />
                <input type="checkbox" name="is_active" value="1" class="accent-blue-500" @checked(old('is_active', $promoPage->is_active ?? true)) />
                Active
            </label>
        </div>
        <div class="flex flex-col gap-2">
            <button type="submit" class="w-full px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors">Simpan Promo</button>
            <a href="{{ route('promo-pages.index') }}" class="w-full text-center px-4 py-2.5 text-sm font-semibold border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50 transition-colors">Batal</a>
        </div>
    </div>
</div>
