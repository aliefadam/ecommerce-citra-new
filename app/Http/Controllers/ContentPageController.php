<?php

namespace App\Http\Controllers;

use App\Models\ContentPage;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentPageController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');

        $contents = ContentPage::query()
            ->when(in_array($type, [ContentPage::TYPE_PAGE, ContentPage::TYPE_POST], true), fn($query) => $query->where('type', $type))
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
        ]);
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request);

        ContentPage::query()->create([
            ...$validated,
            'slug' => $this->makeSlug($validated['slug'] ?: $validated['title']),
            'hero_image' => $this->resolveHeroImage($request, $imageOptimizer),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('content-pages.index')->with('success', 'Konten berhasil dibuat.');
    }

    public function edit(ContentPage $contentPage)
    {
        return view('backend.content-pages.edit', compact('contentPage'));
    }

    public function update(Request $request, ContentPage $contentPage, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request, $contentPage->id);

        $contentPage->update([
            ...$validated,
            'slug' => $this->makeSlug($validated['slug'] ?: $validated['title']),
            'hero_image' => $this->resolveHeroImage($request, $imageOptimizer, $contentPage->hero_image),
        ]);

        return redirect()->route('content-pages.index')->with('success', 'Konten berhasil diperbarui.');
    }

    public function destroy(ContentPage $contentPage)
    {
        $contentPage->delete();

        return back()->with('success', 'Konten berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'type' => ['required', Rule::in([ContentPage::TYPE_PAGE, ContentPage::TYPE_POST])],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('content_pages', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function makeSlug(string $value): string
    {
        return Str::slug($value);
    }

    private function resolveHeroImage(Request $request, ImageOptimizer $imageOptimizer, ?string $fallback = null): ?string
    {
        if ($request->hasFile('hero_image_file')) {
            return asset('storage/' . ltrim($imageOptimizer->storeWebp($request->file('hero_image_file'), 'content', 1600, 900, 82), '/'));
        }

        return trim((string) $request->input('hero_image_url')) ?: $fallback;
    }
}
