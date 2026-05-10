<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()->orderBy('type')->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('backend.banners.create');
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:carousel,side'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $image = $this->resolveImageValue($request, $imageOptimizer, (string) ($validated['image_url'] ?? ''));
        if ($image === null) {
            return back()
                ->withErrors(['image_url' => 'Gambar banner wajib diisi (upload file atau URL).'])
                ->withInput();
        }
        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        $type = $validated['type'];
        if (!$isActiveTarget && $type === 'carousel' && Banner::query()->where('type', 'carousel')->where('is_active', true)->count() === 0) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner carousel aktif.'])
                ->withInput();
        }

        Banner::query()->create([
            'title' => $validated['title'] ?? null,
            'type' => $type,
            'image' => $image,
            'target_url' => $validated['target_url'] ?? null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => $isActiveTarget,
        ]);

        return redirect()->route('banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function edit(Banner $banner)
    {
        return view('backend.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:carousel,side'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:6144'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        $type = $validated['type'];
        if (!$isActiveTarget && $banner->is_active && $banner->type === 'carousel' && !$this->canDeactivate($banner)) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner carousel aktif.'])
                ->withInput();
        }

        $oldImage = (string) $banner->image;
        $image = $this->resolveImageValue($request, $imageOptimizer, (string) ($validated['image_url'] ?? ''), $oldImage);
        if ($image === null) {
            return back()
                ->withErrors(['image_url' => 'Gambar banner wajib diisi (upload file atau URL).'])
                ->withInput();
        }

        $banner->update([
            'title' => $validated['title'] ?? null,
            'type' => $type,
            'image' => $image,
            'target_url' => $validated['target_url'] ?? null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => $isActiveTarget,
        ]);

        if ($image !== $oldImage) {
            $imageOptimizer->deletePublicFile($oldImage);
        }

        return redirect()->route('banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->is_active && $banner->type === 'carousel' && !$this->canDeactivate($banner)) {
            return back()->withErrors(['banner' => 'Minimal harus ada 1 banner carousel aktif.']);
        }

        app(ImageOptimizer::class)->deletePublicFile((string) $banner->image);

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    private function canDeactivate(Banner $banner): bool
    {
        return Banner::query()
            ->where('id', '!=', $banner->id)
            ->where('type', $banner->type)
            ->where('is_active', true)
            ->exists();
    }

    private function resolveImageValue(Request $request, ImageOptimizer $imageOptimizer, string $imageUrl, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $imageOptimizer->storeWebp($request->file('image_file'), 'banners', 1600, 700, 82);
        }

        $trimmed = trim($imageUrl);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return $fallback;
    }
}
