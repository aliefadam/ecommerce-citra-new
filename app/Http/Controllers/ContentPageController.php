<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Models\MainCategory;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContentPageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $contents = ContentPage::query()
            ->when(in_array($type, [ContentPage::TYPE_PAGE, ContentPage::TYPE_POST], true), fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('backend.content-pages.index', compact('contents', 'type'));
    }

    public function create()
    {
        return view('backend.content-pages.create', [
            'contentPage' => new ContentPage([
                'type' => request('type', ContentPage::TYPE_PAGE),
                'is_active' => true,
                'published_at' => now(),
            ]),
            'mainCategories' => MainCategory::query()
                ->with(['categoryDetails' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request);

        $hotspots = $validated['hotspots'] ?? [];
        unset(
            $validated['hotspots'],
            $validated['hero_image_url'],
            $validated['hero_image_file'],
            $validated['diagram_image_url'],
            $validated['diagram_image_file'],
        );

        DB::transaction(function () use ($request, $imageOptimizer, $validated, $hotspots) {
            $contentPage = ContentPage::query()->create([
                ...$validated,
                'slug' => $this->makeSlug($validated['slug'] ?: $validated['title']),
                'hero_image' => $this->resolveHeroImage($request, $imageOptimizer),
                'diagram_image' => $this->resolveDiagramImage($request, $imageOptimizer),
                'created_by' => auth()->id(),
            ]);

            $this->syncHotspots($contentPage, $hotspots);
        });

        return redirect()->route('content-pages.index')->with('success', 'Konten berhasil dibuat.');
    }

    public function edit(ContentPage $contentPage)
    {
        $contentPage->load('categoryHotspots');
        $mainCategories = MainCategory::query()
            ->with(['categoryDetails' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('backend.content-pages.edit', compact('contentPage', 'mainCategories'));
    }

    public function update(Request $request, ContentPage $contentPage, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request, $contentPage->id);

        $hotspots = $validated['hotspots'] ?? [];
        unset(
            $validated['hotspots'],
            $validated['hero_image_url'],
            $validated['hero_image_file'],
            $validated['diagram_image_url'],
            $validated['diagram_image_file'],
        );

        DB::transaction(function () use ($request, $imageOptimizer, $validated, $hotspots, $contentPage) {
            $contentPage->update([
                ...$validated,
                'slug' => $this->makeSlug($validated['slug'] ?: $validated['title']),
                'hero_image' => $this->resolveHeroImage($request, $imageOptimizer, $contentPage->hero_image),
                'diagram_image' => $this->resolveDiagramImage($request, $imageOptimizer, $contentPage->diagram_image),
            ]);

            $this->syncHotspots($contentPage, $hotspots);
        });

        return redirect()->route('content-pages.index')->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(ContentPage $contentPage)
    {
        $contentPage->delete();

        return back()->with('success', 'Konten berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in([ContentPage::TYPE_PAGE, ContentPage::TYPE_POST])],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('content_pages', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'diagram_image_url' => ['nullable', 'url', 'max:2048'],
            'diagram_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'hotspots' => ['nullable', 'array', 'max:30'],
            'hotspots.*.main_category_id' => ['nullable', 'integer', 'exists:main_categories,id'],
            'hotspots.*.category_detail_id' => ['nullable', 'integer', 'exists:category_details,id'],
            'hotspots.*.x_percent' => ['required', 'numeric', 'between:0,100'],
            'hotspots.*.y_percent' => ['required', 'numeric', 'between:0,100'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        foreach ($validated['hotspots'] ?? [] as $index => $hotspot) {
            $hasMainCategory = filled($hotspot['main_category_id'] ?? null);
            $hasCategoryDetail = filled($hotspot['category_detail_id'] ?? null);

            if ($hasMainCategory === $hasCategoryDetail) {
                throw ValidationException::withMessages([
                    "hotspots.$index.main_category_id" => 'Setiap titik harus memilih tepat satu kategori.',
                ]);
            }
        }

        return $validated;
    }

    private function makeSlug(string $value): string
    {
        return Str::slug($value);
    }

    private function resolveHeroImage(Request $request, ImageOptimizer $imageOptimizer, ?string $fallback = null): ?string
    {
        if ($request->hasFile('hero_image_file')) {
            return asset('storage/'.ltrim($imageOptimizer->storeWebp($request->file('hero_image_file'), 'content', 1600, 900, 82), '/'));
        }

        return trim((string) $request->input('hero_image_url')) ?: $fallback;
    }

    private function resolveDiagramImage(Request $request, ImageOptimizer $imageOptimizer, ?string $fallback = null): ?string
    {
        if ($request->hasFile('diagram_image_file')) {
            return asset('storage/'.ltrim($imageOptimizer->storeWebp($request->file('diagram_image_file'), 'content-diagrams', 2000, 1400, 88), '/'));
        }

        return trim((string) $request->input('diagram_image_url')) ?: $fallback;
    }

    private function syncHotspots(ContentPage $contentPage, array $hotspots): void
    {
        $contentPage->categoryHotspots()->delete();

        $contentPage->categoryHotspots()->createMany(array_map(fn (array $hotspot) => [
            'main_category_id' => filled($hotspot['main_category_id'] ?? null) ? (int) $hotspot['main_category_id'] : null,
            'category_detail_id' => filled($hotspot['category_detail_id'] ?? null) ? (int) $hotspot['category_detail_id'] : null,
            'x_percent' => round((float) $hotspot['x_percent'], 2),
            'y_percent' => round((float) $hotspot['y_percent'], 2),
        ], $hotspots));
    }
}
