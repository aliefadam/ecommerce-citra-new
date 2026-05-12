<?php

namespace App\Http\Controllers;

use App\Models\MainCategory;
use App\Models\AttributeDefinition;
use App\Models\CategoryDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequestItem;
use App\Models\TransactionDetail;
use App\Models\Variant;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $attributeDefinitions = AttributeDefinition::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = $categoryDetails;
        return view('backend.products.create', compact('mainCategories', 'categoryDetails', 'categories', 'attributeDefinitions'));
    }

    public function store(Request $request, ImageOptimizer $imageOptimizer)
    {
        $request->merge([
            'variants' => $this->normalizeVariantNumericFields($request->input('variants', [])),
        ]);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'main_category_id'      => ['nullable', 'exists:main_categories,id'],
            'category_detail_id'    => ['nullable', 'exists:category_details,id'],
            'category_id'           => ['nullable', 'exists:category_details,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'is_redeem_product'     => ['nullable', 'boolean'],
            'redeem_points'         => ['nullable', 'integer', 'min:1'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.weight_grams' => ['required', 'integer', 'min:1'],
            'variants.*.length_cm'  => ['nullable', 'numeric', 'min:0'],
            'variants.*.width_cm'   => ['nullable', 'numeric', 'min:0'],
            'variants.*.height_cm'  => ['nullable', 'numeric', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.attributes.*.attribute_definition_id' => ['required', 'exists:attribute_definitions,id'],
            'variants.*.attributes.*.value_text' => ['nullable', 'string', 'max:255'],
            'variants.*.attributes.*.value_number' => ['nullable', 'numeric', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $isRedeemProduct = $request->boolean('is_redeem_product');
        if ($request->boolean('is_redeem_product') && empty($validated['redeem_points'])) {
            return back()
                ->withErrors(['redeem_points' => 'Harga point wajib diisi jika produk redeem diaktifkan.'])
                ->withInput();
        }
        if (!$request->boolean('is_redeem_product')) {
            $validated['redeem_points'] = null;
        }
        $detailId = (int) ($validated['category_detail_id'] ?? $validated['category_id'] ?? 0);
        $detail = CategoryDetail::query()->find($detailId);
        abort_unless($detail, 422);

        $files = $request->file('variants', []);
        $attributeDefinitions = AttributeDefinition::query()->get()->keyBy('id');

        DB::transaction(function () use ($validated, $files, $detail, $imageOptimizer, $attributeDefinitions, $isRedeemProduct) {
            $slug = $this->makeUniqueProductSlug($validated['name']);

            $product = Product::create([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'main_category_id' => (int) $detail->main_category_id,
                'category_detail_id' => (int) $detail->id,
                'category_id' => null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
                'is_redeem_product' => $isRedeemProduct,
                'redeem_points' => $validated['redeem_points'] ?? null,
            ]);

            foreach ($validated['variants'] as $i => $v) {
                $attributes = $v['attributes'] ?? [];
                unset($v['attributes']);
                $v['image'] = isset($files[$i]['image'])
                    ? $imageOptimizer->storeWebp($files[$i]['image'], 'product-variants', 1200, 1200, 82)
                    : null;
                $variantMeta = $this->resolveInternalVariant($attributes, $attributeDefinitions);
                $v['variant_id'] = $variantMeta['variant_id'];
                $v['sku'] = $this->buildVariantSku($validated['name'], $variantMeta['label']);

                $productVariant = $product->productVariants()->create($v);
                $this->syncVariantAttributes($productVariant, $attributes, $attributeDefinitions);
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
        $product->load('productVariants.variant', 'productVariants.attributeValues.definition');
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
        $attributeDefinitions = AttributeDefinition::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $categories = $categoryDetails;
        return view('backend.products.edit', compact('product', 'mainCategories', 'categoryDetails', 'categories', 'attributeDefinitions'));
    }

    public function update(Request $request, Product $product, ImageOptimizer $imageOptimizer)
    {
        $request->merge([
            'variants' => $this->normalizeVariantNumericFields($request->input('variants', [])),
        ]);

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'main_category_id'      => ['nullable', 'exists:main_categories,id'],
            'category_detail_id'    => ['nullable', 'exists:category_details,id'],
            'category_id'           => ['nullable', 'exists:category_details,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'is_redeem_product'     => ['nullable', 'boolean'],
            'redeem_points'         => ['nullable', 'integer', 'min:1'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.product_variant_id' => ['nullable', 'integer'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.weight_grams' => ['required', 'integer', 'min:1'],
            'variants.*.length_cm'  => ['nullable', 'numeric', 'min:0'],
            'variants.*.width_cm'   => ['nullable', 'numeric', 'min:0'],
            'variants.*.height_cm'  => ['nullable', 'numeric', 'min:0'],
            'variants.*.attributes' => ['nullable', 'array'],
            'variants.*.attributes.*.attribute_definition_id' => ['required', 'exists:attribute_definitions,id'],
            'variants.*.attributes.*.value_text' => ['nullable', 'string', 'max:255'],
            'variants.*.attributes.*.value_number' => ['nullable', 'numeric', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        $isRedeemProduct = $request->boolean('is_redeem_product');
        if ($request->boolean('is_redeem_product') && empty($validated['redeem_points'])) {
            return back()
                ->withErrors(['redeem_points' => 'Harga point wajib diisi jika produk redeem diaktifkan.'])
                ->withInput();
        }
        if (!$request->boolean('is_redeem_product')) {
            $validated['redeem_points'] = null;
        }
        $detailId = (int) ($validated['category_detail_id'] ?? $validated['category_id'] ?? 0);
        $detail = CategoryDetail::query()->find($detailId);
        abort_unless($detail, 422);

        $files = $request->file('variants', []);
        $attributeDefinitions = AttributeDefinition::query()->get()->keyBy('id');

        $existingVariants = $product->productVariants()->with('variant', 'attributeValues.definition')->get()->keyBy('id');
        $submittedVariants = collect($validated['variants'])
            ->values()
            ->map(function (array $variant) {
                $variant['product_variant_id'] = (int) ($variant['product_variant_id'] ?? 0) ?: null;
                return $variant;
            });

        $keptVariantIds = $submittedVariants
            ->filter(function (array $variant) use ($existingVariants) {
                $productVariantId = $variant['product_variant_id'] ?? null;
                if (!$productVariantId) {
                    return false;
                }

                return $existingVariants->has($productVariantId);
            })
            ->pluck('product_variant_id')
            ->values();

        $variantIdsToDelete = $existingVariants->keys()
            ->diff($keptVariantIds)
            ->values();

        $variantsInUse = $this->getVariantUsageLabels($existingVariants, $variantIdsToDelete->all());
        if ($variantsInUse !== []) {
            throw ValidationException::withMessages([
                'variants' => 'Varian berikut tidak bisa dihapus karena masih dipakai: ' . implode(', ', $variantsInUse) . '.',
            ]);
        }

        $oldImages = $product->productVariants()->pluck('image')->filter()->values()->all();
        $newImages = [];

        DB::transaction(function () use ($validated, $files, $request, $product, $detail, $imageOptimizer, $existingVariants, $submittedVariants, $keptVariantIds, $variantIdsToDelete, $attributeDefinitions, $isRedeemProduct, &$newImages) {
            $slug = $this->makeUniqueProductSlug($validated['name'], $product->id);

            $product->update([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'main_category_id' => (int) $detail->main_category_id,
                'category_detail_id' => (int) $detail->id,
                'category_id' => null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
                'is_redeem_product' => $isRedeemProduct,
                'redeem_points' => $validated['redeem_points'] ?? null,
            ]);

            if ($variantIdsToDelete->isNotEmpty()) {
                $product->productVariants()
                    ->whereIn('id', $variantIdsToDelete)
                    ->delete();
            }

            foreach ($submittedVariants as $i => $v) {
                $existingVariant = null;
                if (!empty($v['product_variant_id']) && $keptVariantIds->contains($v['product_variant_id'])) {
                    $existingVariant = $existingVariants->get($v['product_variant_id']);
                }
                $attributes = $v['attributes'] ?? [];
                unset($v['attributes']);

                if (isset($files[$i]['image'])) {
                    $v['image'] = $imageOptimizer->storeWebp($files[$i]['image'], 'product-variants', 1200, 1200, 82);
                } else {
                    $existingImage = $request->input("variants.{$i}.existing_image");
                    $v['image'] = $existingImage ?: null;
                }
                if (!empty($v['image'])) {
                    $newImages[] = $v['image'];
                }
                $variantMeta = $this->resolveInternalVariant($attributes, $attributeDefinitions);
                $v['variant_id'] = $variantMeta['variant_id'];
                $v['sku'] = $this->buildVariantSku($validated['name'], $variantMeta['label']);
                unset($v['product_variant_id']);

                if ($existingVariant) {
                    $existingVariant->update($v);
                    $this->syncVariantAttributes($existingVariant, $attributes, $attributeDefinitions);
                    continue;
                }

                $productVariant = $product->productVariants()->create($v);
                $this->syncVariantAttributes($productVariant, $attributes, $attributeDefinitions);
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

    private function buildVariantSku(string $productName, ?string $variantDescriptor): string
    {
        $productPart = Str::upper(Str::slug($productName, '-'));
        $descriptorPart = Str::upper(Str::slug((string) $variantDescriptor, '-'));

        $segments = array_filter([$productPart, $descriptorPart], fn ($s) => $s !== '');

        return implode('-', $segments);
    }

    private function normalizeVariantNumericFields(array $variants): array
    {
        return collect($variants)
            ->map(function ($variant) {
                if (!is_array($variant)) {
                    return $variant;
                }

                foreach (['price', 'stock', 'weight_grams', 'length_cm', 'width_cm', 'height_cm'] as $field) {
                    if (!array_key_exists($field, $variant)) {
                        continue;
                    }

                    if (in_array($field, ['length_cm', 'width_cm', 'height_cm'], true)) {
                        $variant[$field] = $this->normalizeDecimalInput($variant[$field]);
                        continue;
                    }

                    $variant[$field] = preg_replace('/\D+/', '', (string) $variant[$field]) ?? '';
                }

                if (isset($variant['attributes']) && is_array($variant['attributes'])) {
                    $variant['attributes'] = collect($variant['attributes'])
                        ->map(function ($attribute) {
                            if (!is_array($attribute)) {
                                return $attribute;
                            }

                            $attribute['attribute_definition_id'] = (int) ($attribute['attribute_definition_id'] ?? 0) ?: null;
                            $attribute['value_text'] = isset($attribute['value_text']) ? trim((string) $attribute['value_text']) : null;
                            $attribute['value_number'] = $this->normalizeDecimalInput($attribute['value_number'] ?? null);

                            return $attribute;
                        })
                        ->all();
                }

                return $variant;
            })
            ->all();
    }

    private function normalizeDecimalInput(mixed $value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $raw);
        $normalized = preg_replace('/[^0-9.]/', '', $normalized) ?? '';

        if ($normalized === '') {
            return null;
        }

        return $normalized;
    }

    private function resolveInternalVariant(array $attributes, $attributeDefinitions): array
    {
        $label = $this->buildInternalVariantLabel($attributes, $attributeDefinitions);
        $variant = Variant::query()->firstOrCreate([
            'name' => 'Varian SKU',
            'value' => $label,
        ]);

        return [
            'variant_id' => (int) $variant->id,
            'label' => $label,
        ];
    }

    private function buildInternalVariantLabel(array $attributes, $attributeDefinitions): string
    {
        $attributesByDefinition = collect($attributes)
            ->filter(fn ($attribute) => is_array($attribute) && !empty($attribute['attribute_definition_id']))
            ->keyBy(fn ($attribute) => (int) $attribute['attribute_definition_id']);

        $segments = [];

        foreach (['diameter', 'length_mm', 'thread_type', 'grade', 'material'] as $code) {
            $definition = $attributeDefinitions->firstWhere('code', $code);
            if (!$definition) {
                continue;
            }

            $attribute = $attributesByDefinition->get((int) $definition->id);
            if (!$attribute) {
                continue;
            }

            $value = trim((string) ($attribute['value_text'] ?? ''));
            if ($value === '' && isset($attribute['value_number']) && $attribute['value_number'] !== null && $attribute['value_number'] !== '') {
                $value = rtrim(rtrim((string) $attribute['value_number'], '0'), '.');
            }

            if ($value === '') {
                continue;
            }

            $segments[] = match ($code) {
                'length_mm' => $value . 'mm',
                default => $value,
            };
        }

        if ($segments === []) {
            throw ValidationException::withMessages([
                'variants' => 'Setiap varian minimal harus memiliki satu atribut teknis agar kombinasi SKU bisa dibentuk.',
            ]);
        }

        return implode(' - ', $segments);
    }

    private function syncVariantAttributes(ProductVariant $productVariant, array $attributes, $attributeDefinitions): void
    {
        $attributesByDefinition = collect($attributes)
            ->filter(fn ($attribute) => is_array($attribute) && !empty($attribute['attribute_definition_id']))
            ->keyBy(fn ($attribute) => (int) $attribute['attribute_definition_id']);

        $keptDefinitionIds = [];

        foreach ($attributesByDefinition as $definitionId => $attribute) {
            $definition = $attributeDefinitions->get((int) $definitionId);
            if (!$definition) {
                continue;
            }

            $valueText = trim((string) ($attribute['value_text'] ?? ''));
            $valueNumber = $attribute['value_number'] ?? null;
            $hasText = $valueText !== '';
            $hasNumber = $valueNumber !== null && $valueNumber !== '';

            if (!$hasText && !$hasNumber) {
                continue;
            }

            $keptDefinitionIds[] = (int) $definitionId;

            $productVariant->attributeValues()->updateOrCreate(
                ['attribute_definition_id' => (int) $definitionId],
                [
                    'value_text' => $hasText ? $valueText : null,
                    'value_number' => $hasNumber ? $valueNumber : null,
                ]
            );
        }

        $productVariant->attributeValues()
            ->when($keptDefinitionIds !== [], fn ($query) => $query->whereNotIn('attribute_definition_id', $keptDefinitionIds))
            ->when($keptDefinitionIds === [], fn ($query) => $query)
            ->delete();
    }

    private function getVariantUsageLabels($existingVariants, array $variantIds): array
    {
        if ($variantIds === []) {
            return [];
        }

        $usedVariantIds = collect($variantIds)
            ->filter(function (int $variantId) {
                return DB::table('carts')->where('product_variant_id', $variantId)->exists()
                    || DB::table('flash_sale_items')->where('product_variant_id', $variantId)->exists()
                    || DB::table('stock_movements')->where('product_variant_id', $variantId)->exists()
                    || TransactionDetail::query()->where('product_variant_id', $variantId)->exists()
                    || ReturnRequestItem::query()->where('product_variant_id', $variantId)->exists();
            });

        return $usedVariantIds
            ->map(function (int $variantId) use ($existingVariants) {
                $productVariant = $existingVariants->get($variantId);
                $variantName = $productVariant?->attributeSummary() ?? '';

                return $variantName !== '' ? $variantName : 'ID #' . $variantId;
            })
            ->values()
            ->all();
    }
}
