<?php

namespace App\Http\Controllers;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryDetailController extends Controller
{
    public function index()
    {
        $categoryDetails = CategoryDetail::query()->with('mainCategory')->latest()->get();
        $mainCategories  = MainCategory::query()->orderBy('name')->get();
        return view('backend.category-details.index', compact('categoryDetails', 'mainCategories'));
    }

    public function create()
    {
        $mainCategories = MainCategory::query()->orderBy('name')->get();
        return view('backend.category-details.create', compact('mainCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        CategoryDetail::create([
            'main_category_id' => $validated['main_category_id'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
        ]);
        return redirect()->route('category-details.index')->with('success', 'Kategori detail berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(CategoryDetail $categoryDetail)
    {
        $mainCategories = MainCategory::query()->orderBy('name')->get();
        return view('backend.category-details.edit', compact('categoryDetail', 'mainCategories'));
    }

    public function update(Request $request, CategoryDetail $categoryDetail)
    {
        $validated = $request->validate([
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('category_details', 'name')->where('main_category_id', $request->main_category_id)->ignore($categoryDetail->id)],
        ]);
        $categoryDetail->update([
            'main_category_id' => $validated['main_category_id'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $categoryDetail->id),
        ]);
        return redirect()->route('category-details.index')->with('success', 'Kategori detail berhasil diperbarui.');
    }

    public function destroy(CategoryDetail $categoryDetail)
    {
        $categoryDetail->delete();
        return redirect()->route('category-details.index')->with('success', 'Kategori detail berhasil dihapus.');
    }

    public function quickStore(Request $request)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $mainCategoryId = MainCategory::query()->orderBy('id')->value('id');
        abort_unless($mainCategoryId, 422);

        $name = trim((string) $request->name);
        $detail = CategoryDetail::firstOrCreate(
            ['main_category_id' => $mainCategoryId, 'name' => $name],
            ['slug' => $this->uniqueSlug($name)]
        );

        return response()->json(['id' => $detail->id, 'name' => $detail->name]);
    }

    private function uniqueSlug(string $name, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'kategori-detail';
        $slug = $base;
        $counter = 2;
        while (CategoryDetail::query()->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }
}
