<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $wishlists = Wishlist::query()
            ->with(['product.productVariants.variant', 'product.flashSaleItems.flashSale'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $data = $wishlists
            ->map(function (Wishlist $wishlist) {
                $product = $wishlist->product;
                if (!$product) {
                    return null;
                }

                $variant = $product->productVariants->first();
                if (!$variant) {
                    return null;
                }

                $activeFlashSaleItem = $product->flashSaleItems
                    ->first(function ($item) {
                        $sale = $item->flashSale;
                        if (!$sale || !$item->is_active || $sale->status !== 'active') {
                            return false;
                        }
                        return now()->between($sale->start_at, $sale->end_at);
                    });

                $price = $activeFlashSaleItem ? (int) $activeFlashSaleItem->discount_price : (int) $variant->price;

                return [
                    'id' => (int) $wishlist->id,
                    'product_id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                    'price' => $price,
                    'rating' => 0,
                    'image' => $this->resolveImage((string) ($variant->image ?? '')),
                ];
            })
            ->filter()
            ->values()
            ->all();

        return response()->json([
            'items' => $data,
            'count' => count($data),
        ]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = $request->user();
        $productId = (int) $validated['product_id'];
        $exists = Wishlist::query()
            ->where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        $wished = false;
        $message = 'Berhasil dihapus dari wishlist';

        if ($exists) {
            $exists->delete();
        } else {
            Wishlist::query()->create([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
            $wished = true;
            $message = 'Berhasil ditambahkan ke wishlist';
        }

        $count = Wishlist::query()->where('user_id', $user->id)->count();

        return response()->json([
            'wished' => $wished,
            'message' => $message,
            'count' => $count,
        ]);
    }

    public function status(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $productIds = collect($validated['product_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        if ($productIds->isEmpty()) {
            return response()->json([
                'wished_product_ids' => [],
            ]);
        }

        $ids = Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('product_id', $productIds->all())
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return response()->json([
            'wished_product_ids' => $ids,
        ]);
    }

    public function count(Request $request)
    {
        $count = Wishlist::query()
            ->where('user_id', $request->user()->id)
            ->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    private function resolveImage(string $image): string
    {
        if ($image === '') {
            return 'https://via.placeholder.com/300x300?text=No+Image';
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://') ||
            str_starts_with($image, '//') ||
            str_starts_with($image, 'data:')
        ) {
            return $image;
        }

        return asset(ltrim($image, '/'));
    }
}
