<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->with('parent')
            ->latest()
            ->get();

        return view('backend.categories.index', compact('categories'));
    }

    public function create()
    {
        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('backend.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        Category::create([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => $this->makeUniqueSlug($validated['name']),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(Category $category)
    {
        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('backend.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'parent_id' => $validated['parent_id'] ?? null,
            'slug' => $this->makeUniqueSlug($validated['name'], $category->id),
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);

        $name = trim((string) $request->name);
        $category = Category::firstOrCreate(
            ['name' => $name, 'parent_id' => null],
            ['slug' => $this->makeUniqueSlug($name)]
        );

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }

    private function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'kategori';
        $slug = $base;
        $counter = 2;

        while (
            Category::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
