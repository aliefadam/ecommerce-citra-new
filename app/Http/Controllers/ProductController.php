<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'variants.*.sku'        => ['nullable', 'string', 'max:100'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'max:2048'],
        ]);

        $files = $request->file('variants', []);

        DB::transaction(function () use ($validated, $files) {
            $product = Product::create([
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['variants'] as $i => $v) {
                $v['image'] = isset($files[$i]['image'])
                    ? $files[$i]['image']->store('product-variants', 'public')
                    : null;

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
            'variants.*.sku'        => ['nullable', 'string', 'max:100'],
            'variants.*.price'      => ['required', 'numeric', 'min:0'],
            'variants.*.stock'      => ['required', 'integer', 'min:0'],
            'variants.*.image'      => ['nullable', 'image', 'max:2048'],
        ]);

        $files = $request->file('variants', []);

        DB::transaction(function () use ($validated, $files, $request, $product) {
            $product->update([
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'status'      => $validated['status'],
                'description' => $validated['description'] ?? null,
            ]);

            $product->productVariants()->delete();

            foreach ($validated['variants'] as $i => $v) {
                if (isset($files[$i]['image'])) {
                    $v['image'] = $files[$i]['image']->store('product-variants', 'public');
                } else {
                    $existing = $request->input("variants.{$i}.existing_image");
                    $v['image'] = $existing ?: null;
                }

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
}
