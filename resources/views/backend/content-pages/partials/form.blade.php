@php
    $selectedType = old('type', $contentPage->type ?? 'page');
    $oldDiagramHotspots = old('hotspots');
    $diagramHotspots = is_array($oldDiagramHotspots)
        ? collect($oldDiagramHotspots)->map(fn ($hotspot) => [
            'target' => filled($hotspot['category_detail_id'] ?? null)
                ? 'detail:'.$hotspot['category_detail_id']
                : 'main:'.($hotspot['main_category_id'] ?? ''),
            'x_percent' => (float) ($hotspot['x_percent'] ?? 0),
            'y_percent' => (float) ($hotspot['y_percent'] ?? 0),
        ])->values()->all()
        : ($contentPage->relationLoaded('categoryHotspots')
            ? $contentPage->categoryHotspots->map(fn ($hotspot) => [
                'target' => $hotspot->category_detail_id
                    ? 'detail:'.$hotspot->category_detail_id
                    : 'main:'.$hotspot->main_category_id,
                'x_percent' => (float) $hotspot->x_percent,
                'y_percent' => (float) $hotspot->y_percent,
            ])->values()->all()
            : []);
    $diagramCategories = $mainCategories->flatMap(fn ($category) => collect([[
        'key' => 'main:'.$category->id,
        'name' => (string) $category->name.' (semua)',
    ]])->concat($category->categoryDetails->map(fn ($detail) => [
        'key' => 'detail:'.$detail->id,
        'name' => $category->name.' › '.$detail->name,
    ])))->values()->all();
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
            <input type="text" name="slug" value="{{ old('slug', $contentPage->slug ?? '') }}" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" placeholder="kebijakan-privasi">
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

        <section class="space-y-4 border-t border-slate-200 pt-5 dark:border-slate-700"
            x-data="categoryDiagramEditor({{ \Illuminate\Support\Js::from($diagramHotspots) }}, {{ \Illuminate\Support\Js::from($diagramCategories) }}, {{ \Illuminate\Support\Js::from(old('diagram_image_url', $contentPage->diagram_image ?? '')) }})">
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white">Drawing Produk Interaktif</h2>
                <p class="mt-1 text-xs leading-5 text-slate-500">Khusus halaman statis. Pilih kategori, lalu klik posisi komponennya pada drawing. Produk kategori tersebut akan tampil saat titik diklik oleh pengunjung.</p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="diagram_image_url" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">URL Drawing</label>
                    <input id="diagram_image_url" type="url" name="diagram_image_url" value="{{ old('diagram_image_url', $contentPage->diagram_image ?? '') }}"
                        @input="preview = $event.target.value"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50" placeholder="https://...">
                </div>
                <div>
                    <label for="diagram_image_file" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Upload Drawing</label>
                    <input id="diagram_image_file" type="file" name="diagram_image_file" accept="image/jpeg,image/png,image/webp"
                        @change="loadFile($event)"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                <div>
                    <label for="diagram_category" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Kategori yang akan ditandai</label>
                    <select id="diagram_category" x-model="selectedTarget" class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50">
                        <option value="">Pilih kategori</option>
                        <template x-for="category in categories" :key="category.key">
                            <option :value="category.key" x-text="category.name"></option>
                        </template>
                    </select>
                </div>
                <p class="pb-2.5 text-xs font-medium text-blue-600" x-text="selectedTarget ? 'Klik drawing untuk menaruh titik' : 'Pilih kategori terlebih dahulu'"></p>
            </div>

            <div x-show="preview" class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-100" style="display: none;">
                <img :src="preview" alt="Preview drawing produk" class="block h-auto w-full select-none" @click="addHotspot($event)">
                <template x-for="(hotspot, index) in hotspots" :key="hotspot.key">
                    <button type="button" @click.stop="selectedTarget = hotspot.target" :title="categoryName(hotspot.target)"
                        class="absolute grid size-8 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white bg-blue-600 text-xs font-extrabold text-white shadow-lg ring-2 ring-blue-600/30 hover:bg-blue-700 focus:outline-none focus:ring-4"
                        :style="`left: ${hotspot.x_percent}%; top: ${hotspot.y_percent}%`" x-text="index + 1"></button>
                </template>
            </div>

            <div x-show="!preview" class="grid min-h-44 place-items-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-6 text-center text-sm text-slate-400">
                Upload drawing atau masukkan URL untuk mulai menandai kategori.
            </div>

            <div x-show="hotspots.length" class="space-y-2" style="display: none;">
                <template x-for="(hotspot, index) in hotspots" :key="hotspot.key">
                    <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                        <span class="grid size-7 shrink-0 place-items-center rounded-full bg-blue-600 text-xs font-bold text-white" x-text="index + 1"></span>
                        <select x-model="hotspot.target" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <template x-for="category in categories" :key="category.key">
                                <option :value="category.key" x-text="category.name"></option>
                            </template>
                        </select>
                        <span class="hidden text-xs text-slate-400 sm:inline" x-text="`${Number(hotspot.x_percent).toFixed(1)}%, ${Number(hotspot.y_percent).toFixed(1)}%`"></span>
                        <button type="button" @click="removeHotspot(index)" class="rounded-lg px-2 py-1 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>

                        <input type="hidden" :name="`hotspots[${index}][main_category_id]`" :value="hotspot.target.startsWith('main:') ? hotspot.target.split(':')[1] : ''">
                        <input type="hidden" :name="`hotspots[${index}][category_detail_id]`" :value="hotspot.target.startsWith('detail:') ? hotspot.target.split(':')[1] : ''">
                        <input type="hidden" :name="`hotspots[${index}][x_percent]`" :value="hotspot.x_percent">
                        <input type="hidden" :name="`hotspots[${index}][y_percent]`" :value="hotspot.y_percent">
                    </div>
                </template>
            </div>
        </section>
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

<script>
    window.categoryDiagramEditor = function(initialHotspots, categories, initialPreview) {
        return {
            categories,
            hotspots: initialHotspots.map((hotspot, index) => ({ ...hotspot, key: `existing-${index}` })),
            selectedTarget: '',
            preview: initialPreview,
            nextKey: initialHotspots.length,
            loadFile(event) {
                const file = event.target.files?.[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = () => this.preview = reader.result;
                reader.readAsDataURL(file);
            },
            addHotspot(event) {
                if (!this.selectedTarget) return;

                const bounds = event.currentTarget.getBoundingClientRect();
                this.hotspots.push({
                    key: `new-${this.nextKey++}`,
                    target: this.selectedTarget,
                    x_percent: Math.max(0, Math.min(100, ((event.clientX - bounds.left) / bounds.width) * 100)).toFixed(2),
                    y_percent: Math.max(0, Math.min(100, ((event.clientY - bounds.top) / bounds.height) * 100)).toFixed(2),
                });
            },
            removeHotspot(index) {
                this.hotspots.splice(index, 1);
            },
            categoryName(target) {
                return this.categories.find(category => category.key === target)?.name || 'Kategori';
            },
        };
    };
</script>
