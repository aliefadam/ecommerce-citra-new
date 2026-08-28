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
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image_file.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image_file.max' => 'Ukuran file gambar maksimal 12 MB sebelum dikompres.',
        ]);

        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        $type = $validated['type'];
        try {
            $image = $this->resolveImageValue($request, $imageOptimizer, $type, (string) ($validated['image_url'] ?? ''));
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['image_file' => 'Gambar gagal diproses. Gunakan file JPG, PNG, atau WebP yang valid.'])
                ->withInput();
        }
        if ($image === null) {
            return back()
                ->withErrors(['image_url' => 'Gambar banner wajib diisi (upload file atau URL).'])
                ->withInput();
        }
        if (!$isActiveTarget && $type === 'carousel' && Banner::query()->where('type', 'carousel')->where('is_active', true)->count() === 0) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner carousel aktif.'])
                ->withInput();
        }
        if ($isActiveTarget && $type === 'side' && $this->activeSideBannerLimitReached()) {
            return back()
                ->withErrors(['is_active' => 'Maksimal hanya 2 banner kanan yang dapat diaktifkan.'])
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
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:12288'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'image_file.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'image_file.max' => 'Ukuran file gambar maksimal 12 MB sebelum dikompres.',
        ]);

        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        $type = $validated['type'];
        if (!$isActiveTarget && $banner->is_active && $banner->type === 'carousel' && !$this->canDeactivate($banner)) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner carousel aktif.'])
                ->withInput();
        }
        $becomingActiveSide = $isActiveTarget
            && $type === 'side'
            && !($banner->type === 'side' && $banner->is_active);
        if ($becomingActiveSide && $this->activeSideBannerLimitReached($banner)) {
            return back()
                ->withErrors(['is_active' => 'Maksimal hanya 2 banner kanan yang dapat diaktifkan.'])
                ->withInput();
        }

        $oldImage = (string) $banner->image;
        try {
            $image = $this->resolveImageValue($request, $imageOptimizer, $type, (string) ($validated['image_url'] ?? ''), $oldImage);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['image_file' => 'Gambar gagal diproses. Gunakan file JPG, PNG, atau WebP yang valid.'])
                ->withInput();
        }
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

    private function activeSideBannerLimitReached(?Banner $except = null): bool
    {
        return Banner::query()
            ->where('type', 'side')
            ->where('is_active', true)
            ->when($except, fn ($query) => $query->where('id', '!=', $except->getKey()))
            ->count() >= 2;
    }

    private function resolveImageValue(Request $request, ImageOptimizer $imageOptimizer, string $type, string $imageUrl, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            // Both placements use the same 16:7 design canvas. Side banners only need
            // a smaller generated file because their rendered width is much smaller.
            [$w, $h] = $type === 'side' ? [800, 350] : [1600, 700];
            return $imageOptimizer->storeWebpCover($request->file('image_file'), 'banners', $w, $h, 82);
        }

        $trimmed = trim($imageUrl);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return $fallback;
    }
}
