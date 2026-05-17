<?php

namespace App\Http\Controllers;

use App\Models\PromoPage;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromoPageController extends Controller
{
    public function index()
    {
        $promos = PromoPage::query()->latest()->get();

        return view('backend.promo-pages.index', compact('promos'));
    }

    public function create()
    {
        return view('backend.promo-pages.create');
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request);

        PromoPage::query()->create([
            ...$validated,
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'hero_image' => $this->resolveHeroImage($request, $imageOptimizer),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('promo-pages.index')->with('success', 'Promo page berhasil dibuat.');
    }

    public function edit(PromoPage $promoPage)
    {
        return view('backend.promo-pages.edit', compact('promoPage'));
    }

    public function update(Request $request, PromoPage $promoPage, ImageOptimizer $imageOptimizer)
    {
        $validated = $this->validatePayload($request, $promoPage->id);
        $heroImage = $this->resolveHeroImage($request, $imageOptimizer, $promoPage->hero_image);

        $promoPage->update([
            ...$validated,
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'hero_image' => $heroImage,
        ]);

        return redirect()->route('promo-pages.index')->with('success', 'Promo page berhasil diperbarui.');
    }

    public function destroy(PromoPage $promoPage)
    {
        $promoPage->delete();

        return back()->with('success', 'Promo page berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('promo_pages', 'slug')->ignore($ignoreId)],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'hero_image_url' => ['nullable', 'url', 'max:2048'],
            'hero_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'cta_label' => ['nullable', 'string', 'max:50'],
            'cta_url' => ['nullable', 'url', 'max:2048'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveHeroImage(Request $request, ImageOptimizer $imageOptimizer, ?string $fallback = null): ?string
    {
        if ($request->hasFile('hero_image_file')) {
            return asset('storage/' . ltrim($imageOptimizer->storeWebp($request->file('hero_image_file'), 'promo', 1600, 900, 82), '/'));
        }

        return trim((string) $request->input('hero_image_url')) ?: $fallback;
    }
}
