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
        $diagramImage = (string) ($mainCategory->diagram_image ?? '');
        $diagramImageUrl =
            $diagramImage !== '' &&
            (str_starts_with($diagramImage, 'http://') ||
                str_starts_with($diagramImage, 'https://') ||
                str_starts_with($diagramImage, '//') ||
                str_starts_with($diagramImage, 'data:'))
                ? $diagramImage
                : ($diagramImage !== '' ? asset('storage/' . ltrim($diagramImage, '/')) : '');
        $oldDiagramAreas = old('diagram_areas');
        $diagramAreas = is_array($oldDiagramAreas)
            ? collect($oldDiagramAreas)->values()->all()
            : $mainCategory->diagramAreas->map(fn ($area) => [
                'category_detail_id' => (int) $area->category_detail_id,
                'x_percent' => (float) $area->x_percent,
                'y_percent' => (float) $area->y_percent,
                'width_percent' => (float) $area->width_percent,
                'height_percent' => (float) $area->height_percent,
            ])->values()->all();
        $diagramCategoryOptions = $mainCategory->categoryDetails->map(fn ($category) => [
            'id' => (int) $category->id,
            'name' => (string) $category->name,
        ])->values()->all();
    @endphp
    <main class="flex-1 p-4 sm:p-6 mt-6">
        <div class="max-w-6xl bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6">
            <form id="mainCategoryForm" action="{{ route('main-categories.update', $mainCategory) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Category Name</label>
                    <input type="text" name="name" value="{{ old('name', $mainCategory->name) }}"
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
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
                    <input type="text" name="image_url" value="{{ old('image_url', $mainCategory->image) }}" placeholder="https://..."
                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    @error('image_url')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1.5">Template Spesifikasi Default</label>
                    <div class="flex flex-col gap-2 sm:flex-row">
                        <select name="default_specification_template_id" class="min-w-0 flex-1 px-4 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tidak ada default</option>
                            @foreach ($specificationTemplates as $template)<option value="{{ $template->id }}" @selected((string) old('default_specification_template_id', $mainCategory->default_specification_template_id) === (string) $template->id)>{{ $template->name }}</option>@endforeach
                        </select>
                        @if ($mainCategory->default_specification_template_id)
                            <a href="{{ route('specification-templates.edit', $mainCategory->default_specification_template_id) }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/40 dark:text-blue-300">
                                Edit Field Template
                            </a>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-400">Kategori detail dapat menimpa template default ini.</p>
                </div>

                <section class="space-y-4 border-t border-slate-200 pt-5 dark:border-slate-700"
                    x-data="categoryVisualFinderEditor({{ \Illuminate\Support\Js::from($diagramAreas) }}, {{ \Illuminate\Support\Js::from($diagramCategoryOptions) }}, {{ \Illuminate\Support\Js::from($diagramImageUrl) }})">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">Visual Product Finder</h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Upload drawing kategori ini, pilih subkategori, lalu tarik kotak tepat di atas komponennya. Kotak menjadi area klik tanpa marker pada halaman kategori.</p>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label for="diagramImageUrl" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">URL Drawing</label>
                            <input id="diagramImageUrl" type="text" name="diagram_image_url" value="{{ old('diagram_image_url', $mainCategory->diagram_image) }}"
                                @input="preview = $event.target.value"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100" placeholder="https://...">
                        </div>
                        <div>
                            <label for="diagramImageFile" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Upload Drawing</label>
                            <input id="diagramImageFile" type="file" name="diagram_image_file" accept="image/jpeg,image/png,image/webp"
                                @change="loadFile($event)"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                            @error('diagram_image_file')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    @if ($diagramCategoryOptions === [])
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            Buat subkategori terlebih dahulu sebelum menandai komponen pada drawing.
                        </div>
                    @else
                        <div>
                            <label for="diagramAreaCategory" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-200">Subkategori komponen</label>
                            <select id="diagramAreaCategory" x-model="selectedCategoryId"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                                <option value="">Pilih subkategori</option>
                                <template x-for="category in categories" :key="category.id">
                                    <option :value="String(category.id)" x-text="category.name"></option>
                                </template>
                            </select>
                            <p class="mt-1 text-xs text-blue-600" x-text="selectedCategoryId ? 'Tarik kotak pada komponen di drawing.' : 'Pilih subkategori sebelum menggambar area klik.'"></p>
                        </div>

                        <div x-show="preview" x-ref="canvas" style="display: none; touch-action: none;"
                            class="relative cursor-crosshair select-none overflow-hidden rounded-xl border border-slate-300 bg-slate-100"
                            @pointerdown="startDraw($event)" @pointermove="moveDraw($event)" @pointerup="finishDraw($event)" @pointercancel="cancelDraw()">
                            <img :src="preview" alt="Preview drawing kategori" draggable="false" class="block h-auto w-full pointer-events-none">
                            <template x-for="(area, index) in areas" :key="area.key">
                                <button type="button" @pointerdown.stop @click.stop="selectedCategoryId = String(area.category_detail_id)"
                                    class="absolute border-2 border-blue-600 bg-blue-500/20 hover:bg-blue-500/35 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    :title="categoryName(area.category_detail_id)"
                                    :style="areaStyle(area)">
                                    <span class="absolute left-0 top-0 rounded-br bg-blue-600 px-1.5 py-1 text-[10px] font-bold text-white" x-text="categoryName(area.category_detail_id)"></span>
                                </button>
                            </template>
                            <div x-show="draft" class="pointer-events-none absolute border-2 border-dashed border-orange-500 bg-orange-400/20" :style="draft ? areaStyle(draft) : ''"></div>
                        </div>

                        <div x-show="!preview" class="grid min-h-48 place-items-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-400">
                            Masukkan URL atau upload drawing untuk mulai membuat area klik.
                        </div>

                        <div x-show="areas.length" class="grid gap-2 sm:grid-cols-2" style="display: none;">
                            <template x-for="(area, index) in areas" :key="area.key">
                                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 p-2.5">
                                    <span class="min-w-0 flex-1 truncate text-sm font-semibold text-slate-700" x-text="categoryName(area.category_detail_id)"></span>
                                    <button type="button" @click="areas.splice(index, 1)" class="rounded-lg px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                    <input type="hidden" :name="`diagram_areas[${index}][category_detail_id]`" :value="area.category_detail_id">
                                    <input type="hidden" :name="`diagram_areas[${index}][x_percent]`" :value="area.x_percent">
                                    <input type="hidden" :name="`diagram_areas[${index}][y_percent]`" :value="area.y_percent">
                                    <input type="hidden" :name="`diagram_areas[${index}][width_percent]`" :value="area.width_percent">
                                    <input type="hidden" :name="`diagram_areas[${index}][height_percent]`" :value="area.height_percent">
                                </div>
                            </template>
                        </div>
                    @endif
                </section>
                <div class="flex gap-3">
                    <a href="{{ route('main-categories.index') }}"
                        class="px-4 py-2.5 text-sm font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-700">Cancel</a>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-xl">Update</button>
                </div>
            </form>
        </div>
    </main>
@endsection

@section('script')
    @include('backend.main-categories.partials.image-upload-script', ['emptyLabel' => 'Ganti gambar...'])
    <script>
        window.categoryVisualFinderEditor = function(initialAreas, categories, initialPreview) {
            return {
                categories,
                areas: initialAreas.map((area, index) => ({ ...area, key: `area-${index}` })),
                selectedCategoryId: '',
                preview: initialPreview,
                draft: null,
                startPoint: null,
                nextKey: initialAreas.length,
                loadFile(event) {
                    const file = event.target.files?.[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = () => this.preview = reader.result;
                    reader.readAsDataURL(file);
                },
                point(event) {
                    const bounds = this.$refs.canvas.getBoundingClientRect();
                    return {
                        x: Math.max(0, Math.min(100, ((event.clientX - bounds.left) / bounds.width) * 100)),
                        y: Math.max(0, Math.min(100, ((event.clientY - bounds.top) / bounds.height) * 100)),
                    };
                },
                startDraw(event) {
                    if (!this.selectedCategoryId) return;
                    event.currentTarget.setPointerCapture?.(event.pointerId);
                    this.startPoint = this.point(event);
                    this.draft = { x_percent: this.startPoint.x, y_percent: this.startPoint.y, width_percent: 0, height_percent: 0 };
                },
                moveDraw(event) {
                    if (!this.startPoint) return;
                    const point = this.point(event);
                    this.draft = {
                        x_percent: Math.min(this.startPoint.x, point.x),
                        y_percent: Math.min(this.startPoint.y, point.y),
                        width_percent: Math.abs(point.x - this.startPoint.x),
                        height_percent: Math.abs(point.y - this.startPoint.y),
                    };
                },
                finishDraw(event) {
                    if (!this.startPoint) return;
                    this.moveDraw(event);
                    if (this.draft.width_percent >= 0.5 && this.draft.height_percent >= 0.5) {
                        this.areas.push({
                            key: `area-${this.nextKey++}`,
                            category_detail_id: Number(this.selectedCategoryId),
                            x_percent: this.draft.x_percent.toFixed(2),
                            y_percent: this.draft.y_percent.toFixed(2),
                            width_percent: this.draft.width_percent.toFixed(2),
                            height_percent: this.draft.height_percent.toFixed(2),
                        });
                    }
                    this.cancelDraw();
                },
                cancelDraw() {
                    this.startPoint = null;
                    this.draft = null;
                },
                categoryName(id) {
                    return this.categories.find(category => Number(category.id) === Number(id))?.name || 'Subkategori';
                },
                areaStyle(area) {
                    return `left:${area.x_percent}%;top:${area.y_percent}%;width:${area.width_percent}%;height:${area.height_percent}%`;
                },
            };
        };
    </script>
@endsection
