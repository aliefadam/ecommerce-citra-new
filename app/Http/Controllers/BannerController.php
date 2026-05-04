<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::query()->orderBy('sort_order')->orderByDesc('id')->get();

        return view('backend.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('backend.banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $image = $this->resolveImageValue($request, (string) ($validated['image_url'] ?? ''));
        if ($image === null) {
            return back()
                ->withErrors(['image_url' => 'Gambar banner wajib diisi (upload file atau URL).'])
                ->withInput();
        }
        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        if (!$isActiveTarget && Banner::query()->where('is_active', true)->count() === 0) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner aktif.'])
                ->withInput();
        }

        Banner::query()->create([
            'title' => $validated['title'] ?? null,
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

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'max:4096'],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActiveTarget = (bool) ($validated['is_active'] ?? false);
        if (!$isActiveTarget && $banner->is_active && !$this->canDeactivate($banner)) {
            return back()
                ->withErrors(['is_active' => 'Minimal harus ada 1 banner aktif.'])
                ->withInput();
        }

        $image = $this->resolveImageValue($request, (string) ($validated['image_url'] ?? ''), $banner->image);
        if ($image === null) {
            return back()
                ->withErrors(['image_url' => 'Gambar banner wajib diisi (upload file atau URL).'])
                ->withInput();
        }

        $banner->update([
            'title' => $validated['title'] ?? null,
            'image' => $image,
            'target_url' => $validated['target_url'] ?? null,
            'sort_order' => (int) $validated['sort_order'],
            'is_active' => $isActiveTarget,
        ]);

        return redirect()->route('banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->is_active && !$this->canDeactivate($banner)) {
            return back()->withErrors(['banner' => 'Minimal harus ada 1 banner aktif.']);
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    private function canDeactivate(Banner $banner): bool
    {
        $otherActiveCount = Banner::query()
            ->where('id', '!=', $banner->id)
            ->where('is_active', true)
            ->count();

        return $otherActiveCount > 0;
    }

    private function resolveImageValue(Request $request, string $imageUrl, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $request->file('image_file')->store('banners', 'public');
        }

        $trimmed = trim($imageUrl);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return $fallback;
    }
}
