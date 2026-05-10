<?php

namespace App\Http\Controllers;

use App\Models\MainCategory;
use App\Models\CategoryDetail;
use App\Models\Product;
use App\Models\Variant;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['mainCategory', 'categoryDetail', 'productVariants'])->latest()->get();

        return view('backend.products.index', compact('products'));
    }

    public function create()
    {
        $mainCategories = MainCategory::query()->orderBy('name')->get();
        $categoryDetails = CategoryDetail::query()
            ->with('mainCategory')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                $category->name = ($category->mainCategory?->name ? $category->mainCategory->name . ' > ' : '') . $category->name;
                $category->group_name = (string) ($category->mainCategory?->name ?? 'Lainnya');
                $category->detail_name = (string) $category->getOriginal('name');
                return $category;
            });
        $variants   = Variant::orderBy('name')->orderBy('value')->get();

        $categories = $categoryDetails;
        return view('backend.products.create', compact('mainCategories', 'categoryDetails', 'categories', 'variants'));
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'main_category_id'      => ['nullable', 'exists:main_categories,id'],
            'category_detail_id'    => ['nullable', 'exists:category_details,id'],
            'category_id'           => ['nullable', 'exists:category_details,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.variant_id' => ['required', 'exists:variants,id', 'distinct'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $detailId = (int) ($validated['category_detail_id'] ?? $validated['category_id'] ?? 0);
        $detail = CategoryDetail::query()->find($detailId);
        abort_unless($detail, 422);

        $files = $request->file('variants', []);

        DB::transaction(function () use ($validated, $files, $detail, $imageOptimizer) {
            $slug = $this->makeUniqueProductSlug($validated['name']);

            $product = Product::create([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'main_category_id' => (int) $detail->main_category_id,
                'category_detail_id' => (int) $detail->id,
                'category_id' => null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $variantNamesById = Variant::whereIn('id', collect($validated['variants'])->pluck('variant_id'))
                ->get()
                ->keyBy('id');

            foreach ($validated['variants'] as $i => $v) {
                $v['image'] = isset($files[$i]['image'])
                    ? $imageOptimizer->storeWebp($files[$i]['image'], 'product-variants', 1200, 1200, 82)
                    : null;
                $variant = $variantNamesById->get($v['variant_id']);
                $v['sku'] = $this->buildVariantSku($validated['name'], $variant?->name, $variant?->value);

                $product->productVariants()->create($v);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product berhasil ditambahkan.');
    }

    public function show()
    {
        abort(404);
    }

    public function edit(Product $product)
    {
        $product->load('productVariants.variant');
        $mainCategories = MainCategory::query()->orderBy('name')->get();
        $categoryDetails = CategoryDetail::query()
            ->with('mainCategory')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                $category->name = ($category->mainCategory?->name ? $category->mainCategory->name . ' > ' : '') . $category->name;
                $category->group_name = (string) ($category->mainCategory?->name ?? 'Lainnya');
                $category->detail_name = (string) $category->getOriginal('name');
                return $category;
            });
        $variants   = Variant::orderBy('name')->orderBy('value')->get();

        $categories = $categoryDetails;
        return view('backend.products.edit', compact('product', 'mainCategories', 'categoryDetails', 'categories', 'variants'));
    }

    public function update(Request $request, Product $product, ImageOptimizer $imageOptimizer)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'main_category_id'      => ['nullable', 'exists:main_categories,id'],
            'category_detail_id'    => ['nullable', 'exists:category_details,id'],
            'category_id'           => ['nullable', 'exists:category_details,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.variant_id' => ['required', 'exists:variants,id', 'distinct'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $detailId = (int) ($validated['category_detail_id'] ?? $validated['category_id'] ?? 0);
        $detail = CategoryDetail::query()->find($detailId);
        abort_unless($detail, 422);

        $files = $request->file('variants', []);

        $oldImages = $product->productVariants()->pluck('image')->filter()->values()->all();
        $newImages = [];

        DB::transaction(function () use ($validated, $files, $request, $product, $detail, $imageOptimizer, &$newImages) {
            $slug = $this->makeUniqueProductSlug($validated['name'], $product->id);

            $product->update([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'main_category_id' => (int) $detail->main_category_id,
                'category_detail_id' => (int) $detail->id,
                'category_id' => null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $product->productVariants()->delete();

            $variantNamesById = Variant::whereIn('id', collect($validated['variants'])->pluck('variant_id'))
                ->get()
                ->keyBy('id');

            foreach ($validated['variants'] as $i => $v) {
                if (isset($files[$i]['image'])) {
                    $v['image'] = $imageOptimizer->storeWebp($files[$i]['image'], 'product-variants', 1200, 1200, 82);
                } else {
                    $existing = $request->input("variants.{$i}.existing_image");
                    $v['image'] = $existing ?: null;
                }
                if (!empty($v['image'])) {
                    $newImages[] = $v['image'];
                }
                $variant = $variantNamesById->get($v['variant_id']);
                $v['sku'] = $this->buildVariantSku($validated['name'], $variant?->name, $variant?->value);

                $product->productVariants()->create($v);
            }
        });

        collect($oldImages)
            ->diff($newImages)
            ->each(fn($path) => $imageOptimizer->deletePublicFile((string) $path));

        return redirect()->route('products.index')->with('success', 'Product berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $imageOptimizer = app(ImageOptimizer::class);
        $product->productVariants()
            ->pluck('image')
            ->filter()
            ->each(fn($path) => $imageOptimizer->deletePublicFile((string) $path));

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product berhasil dihapus.');
    }

    private function makeUniqueProductSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $base = $base !== '' ? $base : 'product';
        $slug = $base;
        $counter = 2;

        while (
            Product::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function buildVariantSku(string $productName, ?string $variantName, ?string $variantValue): string
    {
        $productPart = Str::upper(Str::slug($productName, '-'));
        $namePart = Str::upper(Str::slug((string) $variantName, '-'));
        $valuePart = Str::upper(Str::slug((string) $variantValue, '-'));

        $segments = array_filter([$productPart, $namePart, $valuePart], fn ($s) => $s !== '');

        return implode('-', $segments);
    }
}
