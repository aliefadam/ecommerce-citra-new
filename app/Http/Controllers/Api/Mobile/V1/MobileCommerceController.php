<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Mobile\V1\MobileProductListResource;
use App\Models\Cart;
use App\Models\FlashSaleItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Wishlist;
use App\Support\CatalogImageUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileCommerceController extends Controller
{
    public function cart(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->cartData((int) $request->user()->id)]);
    }

    public function addCartItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        $userId = (int) $request->user()->id;

        DB::transaction(function () use ($validated, $userId) {
            $variant = ProductVariant::query()
                ->with('product.company')
                ->lockForUpdate()
                ->findOrFail((int) $validated['product_variant_id']);
            $cart = Cart::query()
                ->where('user_id', $userId)
                ->where('product_variant_id', $variant->id)
                ->lockForUpdate()
                ->first();
            $quantity = (int) ($cart?->quantity ?? 0) + (int) $validated['quantity'];

            $this->ensurePurchasable($variant, $quantity);

            Cart::query()->updateOrCreate([
                'user_id' => $userId,
                'product_variant_id' => $variant->id,
            ], ['quantity' => $quantity]);
        });

        return response()->json(['data' => $this->cartData($userId)], 201);
    }

    public function updateCartItem(Request $request, int $cart): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);
        $userId = (int) $request->user()->id;

        DB::transaction(function () use ($cart, $validated, $userId) {
            $row = Cart::query()
                ->where('user_id', $userId)
                ->whereKey($cart)
                ->lockForUpdate()
                ->firstOrFail();
            $variant = ProductVariant::query()
                ->with('product.company')
                ->lockForUpdate()
                ->findOrFail($row->product_variant_id);

            $this->ensurePurchasable($variant, (int) $validated['quantity']);
            $row->update(['quantity' => (int) $validated['quantity']]);
        });

        return response()->json(['data' => $this->cartData($userId)]);
    }

    public function deleteCartItem(Request $request, int $cart): JsonResponse
    {
        Cart::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($cart)
            ->firstOrFail()
            ->delete();

        return response()->json(null, 204);
    }

    public function clearCart(Request $request): JsonResponse
    {
        Cart::query()->where('user_id', $request->user()->id)->delete();

        return response()->json(null, 204);
    }

    public function wishlist(Request $request): JsonResponse
    {
        $products = Product::query()
            ->storefrontVisible()
            ->whereHas('productVariants')
            ->whereHas('wishlists', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with([
                'company:id,name,slug',
                'mainCategory',
                'categoryDetail',
                'productVariants.variant',
                'productVariants.attributeValues.definition',
            ])
            ->orderByDesc('products.id')
            ->get();

        return response()->json([
            'data' => MobileProductListResource::collection($products)->resolve($request),
            'meta' => ['count' => $products->count()],
        ]);
    }

    public function addWishlist(Request $request, string $slug): JsonResponse
    {
        $product = Product::query()
            ->storefrontVisible()
            ->whereHas('productVariants')
            ->where('slug', $slug)
            ->firstOrFail();

        Wishlist::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return response()->json(['data' => ['wished' => true]], 201);
    }

    public function deleteWishlist(Request $request, string $slug): JsonResponse
    {
        $productId = Product::query()->where('slug', $slug)->value('id');
        if ($productId !== null) {
            Wishlist::query()
                ->where('user_id', $request->user()->id)
                ->where('product_id', $productId)
                ->delete();
        }

        return response()->json(null, 204);
    }

    private function cartData(int $userId): array
    {
        $rows = Cart::query()
            ->with([
                'productVariant.product.company',
                'productVariant.variant',
                'productVariant.attributeValues.definition',
                'productVariant.flashSaleItems.flashSale',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        $items = $rows->map(function (Cart $cart) {
            $variant = $cart->productVariant;
            $product = $variant?->product;
            $companyActive = (bool) $product?->company?->is_active;
            $availableStock = max(0, (int) ($variant?->stock ?? 0));
            $available = $variant && $product && $product->status === 'active' && $companyActive
                && $availableStock >= (int) $cart->quantity;
            $issue = match (true) {
                ! $variant || ! $product || $product->status !== 'active' || ! $companyActive => 'Produk sudah tidak tersedia.',
                $availableStock < 1 => 'Stok produk sedang habis.',
                $availableStock < (int) $cart->quantity => 'Jumlah melebihi stok yang tersedia.',
                default => null,
            };
            $basePrice = (int) ($variant?->price ?? 0);
            $price = $variant && $product ? $this->effectivePrice($variant, (int) $cart->quantity, $product->company_id) : $basePrice;

            return [
                'id' => (int) $cart->id,
                'quantity' => (int) $cart->quantity,
                'available' => $available,
                'issue' => $issue,
                'max_quantity' => $availableStock,
                'unit_price' => $price,
                'subtotal' => $price * (int) $cart->quantity,
                'product' => [
                    'name' => (string) ($product?->name ?? 'Produk tidak tersedia'),
                    'slug' => (string) ($product?->slug ?? ''),
                    'seller' => (string) ($product?->company?->name ?? ''),
                    'image' => CatalogImageUrl::make($variant?->image ?: $product?->firstAvailableImagePath()),
                ],
                'variant' => [
                    'id' => $variant ? (int) $variant->id : null,
                    'sku' => $variant?->sku,
                    'summary' => $variant?->attributeSummary(),
                ],
            ];
        })->values();

        return [
            'items' => $items,
            'summary' => [
                'line_count' => $items->count(),
                'quantity' => $items->sum('quantity'),
                'subtotal' => $items->where('available', true)->sum('subtotal'),
                'has_issues' => $items->contains('available', false),
            ],
        ];
    }

    private function ensurePurchasable(ProductVariant $variant, int $quantity): void
    {
        $product = $variant->product;
        if (! $product || $product->status !== 'active' || ! $product->company?->is_active) {
            throw ValidationException::withMessages(['product_variant_id' => 'Produk ini sudah tidak tersedia.']);
        }

        $stock = max(0, (int) $variant->stock);
        if ($stock < 1) {
            throw ValidationException::withMessages(['quantity' => 'Stok produk ini sedang habis.']);
        }

        if ($quantity > $stock) {
            throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
        }
    }

    private function effectivePrice(ProductVariant $variant, int $quantity, int $companyId): int
    {
        $item = $variant->flashSaleItems->first(function (FlashSaleItem $item) use ($quantity, $companyId) {
            $sale = $item->flashSale;

            return $sale && $item->is_active && $sale->status === 'active'
                && (int) $sale->company_id === $companyId
                && $sale->start_at && $sale->end_at && now()->between($sale->start_at, $sale->end_at)
                && ((int) $item->sold + $quantity) <= (int) $item->quota;
        });

        return $item ? (int) $item->discount_price : (int) $variant->price;
    }
}
