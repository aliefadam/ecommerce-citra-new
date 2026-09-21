<?php

namespace App\Http\Controllers;

use App\Models\CategoryDetail;
use App\Models\MainCategory;
use App\Models\SpecificationTemplate;
use App\Services\CategorySpecificationTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryDetailController extends Controller
{
    public function index()
    {
        $categoryDetails = CategoryDetail::query()->with(['mainCategory', 'specificationTemplate'])->latest()->get();
        $mainCategories = MainCategory::query()->orderBy('name')->get();

        return view('backend.category-details.index', compact('categoryDetails', 'mainCategories'));
    }

    public function create()
    {
        $mainCategories = MainCategory::query()->orderBy('name')->get();

        return view('backend.category-details.create', compact('mainCategories'));
    }

    public function store(Request $request, CategorySpecificationTemplateService $templateService)
    {
        $validated = $request->validate([
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);
        [$categoryDetail, $template] = DB::transaction(function () use ($validated, $templateService) {
            $template = $templateService->createFor($validated['name'], 'kategori detail');
            $categoryDetail = CategoryDetail::create([
                'main_category_id' => $validated['main_category_id'],
                'name' => $validated['name'],
                'slug' => $this->uniqueSlug($validated['name']),
                'specification_template_id' => $template->id,
            ]);

            return [$categoryDetail, $template];
        });

        return redirect()->route('specification-templates.edit', $template)
            ->with('success', "Kategori detail {$categoryDetail->name} dan template spesifikasinya berhasil dibuat. Pilih field yang diperlukan.");
    }

    public function show()
    {
        abort(404);
    }

    public function edit(CategoryDetail $categoryDetail)
    {
        $mainCategories = MainCategory::query()->orderBy('name')->get();
        $specificationTemplates = SpecificationTemplate::query()->where('is_active', true)->orderBy('name')->get();

        return view('backend.category-details.edit', compact('categoryDetail', 'mainCategories', 'specificationTemplates'));
    }

    public function update(Request $request, CategoryDetail $categoryDetail)
    {
        $validated = $request->validate([
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('category_details', 'name')->where('main_category_id', $request->main_category_id)->ignore($categoryDetail->id)],
            'specification_template_id' => ['nullable', 'exists:specification_templates,id'],
        ]);
        $categoryDetail->update([
            'main_category_id' => $validated['main_category_id'],
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name'], $categoryDetail->id),
            'specification_template_id' => $validated['specification_template_id'] ?? null,
        ]);

        return redirect()->route('category-details.index')->with('success', 'Kategori detail berhasil diperbarui.');
    }

    public function destroy(CategoryDetail $categoryDetail)
    {
        $categoryDetail->delete();

        return redirect()->route('category-details.index')->with('success', 'Kategori detail berhasil dihapus.');
    }

    public function quickStore(Request $request, CategorySpecificationTemplateService $templateService)
    {
        $request->validate(['name' => ['required', 'string', 'max:255']]);
        $mainCategoryId = MainCategory::query()->orderBy('id')->value('id');
        abort_unless($mainCategoryId, 422);

        $name = trim((string) $request->name);
        $detail = DB::transaction(function () use ($mainCategoryId, $name, $templateService) {
            $existing = CategoryDetail::query()
                ->where('main_category_id', $mainCategoryId)
                ->where('name', $name)
                ->first();

            if ($existing) {
                return $existing;
            }

            $template = $templateService->createFor($name, 'kategori detail');

            return CategoryDetail::create([
                'main_category_id' => $mainCategoryId,
                'name' => $name,
                'slug' => $this->uniqueSlug($name),
                'specification_template_id' => $template->id,
            ]);
        });

        return response()->json([
            'id' => $detail->id,
            'name' => $detail->name,
            'template_id' => $detail->specification_template_id,
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignore = null): string
    {
        $base = Str::slug($name) ?: 'kategori-detail';
        $slug = $base;
        $counter = 2;
        while (CategoryDetail::query()->when($ignore, fn ($q) => $q->where('id', '!=', $ignore))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }
}
