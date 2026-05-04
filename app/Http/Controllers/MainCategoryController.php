<?php

namespace App\Http\Controllers;

use App\Models\MainCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MainCategoryController extends Controller
{
    public function index()
    {
        $mainCategories = MainCategory::query()->latest()->get();
        return view('backend.main-categories.index', compact('mainCategories'));
    }

    public function create()
    {
        return view('backend.main-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('main_categories', 'name')],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $image = $this->resolveImageValue($request, (string) ($validated['image_url'] ?? ''));

        MainCategory::create([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
            'image' => $image,
        ]);
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(MainCategory $mainCategory)
    {
        return view('backend.main-categories.edit', compact('mainCategory'));
    }

    public function update(Request $request, MainCategory $mainCategory)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('main_categories', 'name')->ignore($mainCategory->id)],
            'image_url' => ['nullable', 'string', 'max:2048'],
            'image_file' => ['nullable', 'image', 'max:4096'],
        ]);

        $image = $this->resolveImageValue($request, (string) ($validated['image_url'] ?? ''), $mainCategory->image);

        $mainCategory->update([
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $mainCategory->id),
            'image' => $image,
        ]);
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil diperbarui.');
    }

    public function destroy(MainCategory $mainCategory)
    {
        $mainCategory->delete();
        return redirect()->route('main-categories.index')->with('success', 'Kategori utama berhasil dihapus.');
    }

    private function uniqueSlug(string $name, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'kategori-utama';
        $slug = $base;
        $counter = 2;
        while (MainCategory::query()->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }

    private function resolveImageValue(Request $request, string $imageUrl, ?string $fallback = null): ?string
    {
        if ($request->hasFile('image_file')) {
            return $request->file('image_file')->store('main-categories', 'public');
        }

        $trimmed = trim($imageUrl);
        if ($trimmed !== '') {
            return $trimmed;
        }

        return $fallback;
    }
}
