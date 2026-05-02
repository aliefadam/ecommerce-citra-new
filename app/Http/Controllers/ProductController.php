<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'productVariants'])->latest()->get();

        return view('backend.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $variants   = Variant::orderBy('name')->orderBy('value')->get();

        return view('backend.products.create', compact('categories', 'variants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'category_id'           => ['nullable', 'exists:categories,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.variant_id' => ['required', 'exists:variants,id', 'distinct'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'max:2048'],
        ]);

        $files = $request->file('variants', []);

        DB::transaction(function () use ($validated, $files) {
            $slug = $this->makeUniqueProductSlug($validated['name']);

            $product = Product::create([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'category_id' => $validated['category_id'] ?? null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $variantNamesById = Variant::whereIn('id', collect($validated['variants'])->pluck('variant_id'))
                ->get()
                ->keyBy('id');

            foreach ($validated['variants'] as $i => $v) {
                $v['image'] = isset($files[$i]['image'])
                    ? $files[$i]['image']->store('product-variants', 'public')
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
        $categories = Category::orderBy('name')->get();
        $variants   = Variant::orderBy('name')->orderBy('value')->get();

        return view('backend.products.edit', compact('product', 'categories', 'variants'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'category_id'           => ['nullable', 'exists:categories,id'],
            'status'                => ['required', Rule::in(['active', 'inactive'])],
            'description'           => ['nullable', 'string'],
            'variants'              => ['required', 'array', 'min:1'],
            'variants.*.variant_id' => ['required', 'exists:variants,id', 'distinct'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'max:2048'],
        ]);

        $files = $request->file('variants', []);

        DB::transaction(function () use ($validated, $files, $request, $product) {
            $slug = $this->makeUniqueProductSlug($validated['name'], $product->id);

            $product->update([
                'name'        => $validated['name'],
                'slug'        => $slug,
                'category_id' => $validated['category_id'] ?? null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $product->productVariants()->delete();

            $variantNamesById = Variant::whereIn('id', collect($validated['variants'])->pluck('variant_id'))
                ->get()
                ->keyBy('id');

            foreach ($validated['variants'] as $i => $v) {
                if (isset($files[$i]['image'])) {
                    $v['image'] = $files[$i]['image']->store('product-variants', 'public');
                } else {
                    $existing = $request->input("variants.{$i}.existing_image");
                    $v['image'] = $existing ?: null;
                }
                $variant = $variantNamesById->get($v['variant_id']);
                $v['sku'] = $this->buildVariantSku($validated['name'], $variant?->name, $variant?->value);

                $product->productVariants()->create($v);
            }
        });

        return redirect()->route('products.index')->with('success', 'Product berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
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
