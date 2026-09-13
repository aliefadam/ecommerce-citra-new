<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StorefrontSearchSuggestionController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $term = mb_strtolower(trim($validated['q']));
        $like = '%'.$term.'%';

        $products = Product::query()
            ->storefrontVisible()
            ->whereNotNull('slug')
            ->with([
                'company:id,name',
                'mainCategory:id,name',
                'categoryDetail:id,name',
                'productVariants' => fn ($query) => $query
                    ->with(['variant:id,name,value', 'attributeValues.definition:id,name,code'])
                    ->orderBy('price'),
            ])
            ->where(function ($query) use ($like): void {
                $query
                    ->whereRaw('LOWER(name) like ?', [$like])
                    ->orWhereHas('mainCategory', fn ($category) => $category->whereRaw('LOWER(name) like ?', [$like]))
                    ->orWhereHas('categoryDetail', fn ($category) => $category->whereRaw('LOWER(name) like ?', [$like]))
                    ->orWhereHas('productVariants', function ($variant) use ($like): void {
                        $variant
                            ->whereRaw('LOWER(COALESCE(sku, ?)) like ?', ['', $like])
                            ->orWhereHas('variant', fn ($value) => $value
                                ->whereRaw('LOWER(name) like ?', [$like])
                                ->orWhereRaw('LOWER(value) like ?', [$like]))
                            ->orWhereHas('attributeValues', fn ($attribute) => $attribute
                                ->whereRaw('LOWER(COALESCE(value_text, ?)) like ?', ['', $like])
                                ->orWhereRaw('CAST(value_number AS CHAR) like ?', [$like])
                                ->orWhereHas('definition', fn ($definition) => $definition->whereRaw('LOWER(name) like ?', [$like])));
                    });
            })
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return response()->json([
            'data' => $products->map(function (Product $product): array {
                $variant = $product->productVariants->first();
                $image = trim((string) ($product->firstAvailableImagePath() ?? ''));

                if ($image === '') {
                    $image = asset('imgs/product-placeholder.svg');
                } elseif (! str_starts_with($image, 'http://') && ! str_starts_with($image, 'https://')) {
                    $image = asset('storage/'.ltrim($image, '/'));
                }

                return [
                    'name' => (string) $product->name,
                    'sku' => (string) ($variant?->sku ?: 'SKU belum tersedia'),
                    'variant' => (string) ($variant?->attributeSummary() ?: 'Varian standar'),
                    'category' => (string) ($product->categoryDetail?->name ?? $product->mainCategory?->name ?? 'Produk'),
                    'company' => (string) ($product->company?->name ?? ''),
                    'price' => $variant ? (int) $variant->price : null,
                    'price_label' => $variant ? 'Mulai Rp'.number_format((int) $variant->price, 0, ',', '.') : 'Hubungi kami',
                    'image' => $image,
                    'url' => route('frontend.detail-produk', ['slug' => $product->slug]),
                ];
            })->values(),
        ]);
    }
}
