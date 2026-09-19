<?php

namespace App\Services;

use App\Models\FlashSaleItem;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutPricingService
{
    /**
     * Resolve checkout lines from database. Client-provided labels and prices are
     * deliberately ignored.
     *
     * @return array{items: array<int, array<string, mixed>>, subtotal: int, weight_grams: int, fingerprint: string}
     */
    public function resolve(array $requestedItems, int $companyId, bool $redeemCheckout = false): array
    {
        $requests = collect($requestedItems)
            ->map(fn (array $item) => [
                'variant_id' => (int) ($item['productVariantId'] ?? 0),
                'quantity' => max(1, (int) ($item['qty'] ?? 1)),
                'note' => mb_substr(trim((string) ($item['note'] ?? '')), 0, 500),
            ]);

        if ($requests->contains(fn (array $item) => $item['variant_id'] < 1)) {
            throw ValidationException::withMessages([
                'items' => 'Setiap item checkout harus memiliki varian produk yang valid.',
            ]);
        }

        $requests = $requests
            ->groupBy('variant_id')
            ->map(fn (Collection $rows) => [
                'variant_id' => (int) $rows->first()['variant_id'],
                'quantity' => (int) $rows->sum('quantity'),
                'note' => (string) $rows->first()['note'],
            ])
            ->sortBy('variant_id')
            ->values();

        $variants = ProductVariant::query()
            ->with([
                'product.productVariants',
                'variant',
                'attributeValues.definition',
                'flashSaleItems.flashSale',
            ])
            ->whereIn('id', $requests->pluck('variant_id'))
            ->get()
            ->keyBy('id');

        $items = $requests->map(function (array $request) use ($variants, $companyId, $redeemCheckout) {
            /** @var ProductVariant|null $variant */
            $variant = $variants->get($request['variant_id']);
            $product = $variant?->product;

            if (! $variant || ! $product || $product->status !== 'active' || (int) $product->company_id !== $companyId) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk sudah tidak tersedia.',
                ]);
            }

            if ((int) $variant->stock < $request['quantity']) {
                throw ValidationException::withMessages([
                    'items' => (int) $variant->stock < 1
                        ? 'Stok salah satu produk sudah habis.'
                        : 'Jumlah produk melebihi stok yang tersedia ('.(int) $variant->stock.').',
                ]);
            }

            if ($redeemCheckout && (! $product->is_redeem_product || (int) $product->redeem_points < 1)) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu produk tidak tersedia untuk penukaran poin.',
                ]);
            }

            $flashSaleItem = $this->activeFlashSaleItem($variant, $request['quantity'], $companyId);
            $price = $flashSaleItem ? (int) $flashSaleItem->discount_price : (int) $variant->price;
            $image = trim((string) ($variant->image ?: $product->firstAvailableImagePath() ?: ''));
            if ($image !== '' && ! Str::startsWith($image, ['http://', 'https://', '//', 'data:'])) {
                $image = asset('storage/'.ltrim($image, '/'));
            }

            return [
                'id' => (int) $product->id,
                'productVariantId' => (int) $variant->id,
                'companyId' => (int) $product->company_id,
                'name' => (string) $product->name,
                'variant' => $variant->attributeSummary(),
                'image' => $image,
                'price' => $price,
                'qty' => $request['quantity'],
                'note' => $request['note'],
                'redeemPoints' => $redeemCheckout ? (int) $product->redeem_points : 0,
                'isFlashSale' => $flashSaleItem !== null,
                'weightGrams' => max(1, (int) ($variant->weight_grams ?: config('services.checkout.default_item_weight', 1000))),
            ];
        })->values();

        $fingerprintRows = $items->map(fn (array $item) => [
            'variant_id' => $item['productVariantId'],
            'quantity' => $item['qty'],
        ])->all();

        return [
            'items' => $items->all(),
            'subtotal' => (int) $items->sum(fn (array $item) => $item['price'] * $item['qty']),
            'weight_grams' => (int) $items->sum(fn (array $item) => $item['weightGrams'] * $item['qty']),
            'fingerprint' => hash('sha256', json_encode([
                'company_id' => $companyId,
                'redeem_checkout' => $redeemCheckout,
                'items' => $fingerprintRows,
            ], JSON_THROW_ON_ERROR)),
        ];
    }

    private function activeFlashSaleItem(ProductVariant $variant, int $quantity, int $companyId): ?FlashSaleItem
    {
        return $variant->flashSaleItems->first(function (FlashSaleItem $item) use ($quantity, $companyId) {
            $sale = $item->flashSale;

            return $sale
                && $item->is_active
                && $sale->status === 'active'
                && (int) $sale->company_id === $companyId
                && $sale->start_at
                && $sale->end_at
                && now()->between($sale->start_at, $sale->end_at)
                && ((int) $item->sold + $quantity) <= (int) $item->quota;
        });
    }
}
