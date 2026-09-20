<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\FlashSaleItem;
use App\Models\FlashSaleReservation;
use App\Models\InventoryReservation;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommerceReservationService
{
    public function assertPayable(Transaction $transaction): void
    {
        $statuses = InventoryReservation::query()
            ->where('transaction_id', $transaction->id)
            ->lockForUpdate()
            ->pluck('status');

        if ($statuses->isNotEmpty() && $statuses->every(fn (string $status): bool => $status === 'released')) {
            throw ValidationException::withMessages([
                'stock' => 'Pembayaran ditolak karena reservasi stok transaksi sudah dilepas.',
            ]);
        }
    }

    public function lockUsableCoupon(int $companyId, string $code, int $subtotal, bool $guest): Coupon
    {
        $normalized = Coupon::normalizeCode($code);
        $coupon = Coupon::query()
            ->where('company_id', $companyId)
            ->where('normalized_code', $normalized)
            ->lockForUpdate()
            ->first();

        $reserved = $coupon
            ? CouponRedemption::query()->where('coupon_id', $coupon->id)->where('status', 'reserved')->count()
            : 0;

        if (! $coupon || ($guest && $coupon->is_member_only) || ! $coupon->isUsableFor($subtotal, $reserved)) {
            throw ValidationException::withMessages(['coupon' => 'Voucher tidak valid atau sudah tidak bisa digunakan.']);
        }

        return $coupon;
    }

    public function reserve(Transaction $transaction, array $items, ?Coupon $coupon = null, int $discountAmount = 0): void
    {
        foreach (collect($items)->sortBy('productVariantId') as $item) {
            $variantId = (int) ($item['productVariantId'] ?? 0);
            $quantity = max(1, (int) ($item['qty'] ?? 1));
            $variant = ProductVariant::query()->lockForUpdate()->find($variantId);

            if (! $variant) {
                throw ValidationException::withMessages(['items' => 'Salah satu varian produk sudah tidak tersedia.']);
            }

            $alreadyReserved = (int) InventoryReservation::query()
                ->where('product_variant_id', $variantId)
                ->where('status', 'reserved')
                ->sum('quantity');
            $available = max(0, (int) $variant->stock - $alreadyReserved);
            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'items' => $available < 1 ? 'Stok salah satu produk sudah habis atau sedang dipesan.' : "Stok tersedia hanya {$available}.",
                ]);
            }

            InventoryReservation::query()->create([
                'transaction_id' => $transaction->id,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'status' => 'reserved',
                'expires_at' => $transaction->expires_at,
            ]);

            $flashSaleItemId = (int) ($item['flashSaleItemId'] ?? 0);
            if ($flashSaleItemId > 0) {
                $flashItem = FlashSaleItem::query()->lockForUpdate()->find($flashSaleItemId);
                $reservedQuota = $flashItem
                    ? (int) FlashSaleReservation::query()->where('flash_sale_item_id', $flashItem->id)->where('status', 'reserved')->sum('quantity')
                    : 0;

                if (! $flashItem || ((int) $flashItem->sold + $reservedQuota + $quantity) > (int) $flashItem->quota) {
                    throw ValidationException::withMessages(['items' => 'Kuota flash sale baru saja habis. Muat ulang checkout untuk memakai harga reguler.']);
                }

                FlashSaleReservation::query()->create([
                    'transaction_id' => $transaction->id,
                    'flash_sale_item_id' => $flashItem->id,
                    'quantity' => $quantity,
                    'status' => 'reserved',
                    'expires_at' => $transaction->expires_at,
                ]);
            }
        }

        if ($coupon && $discountAmount > 0) {
            CouponRedemption::query()->create([
                'transaction_id' => $transaction->id,
                'coupon_id' => $coupon->id,
                'discount_amount' => $discountAmount,
                'status' => 'reserved',
                'expires_at' => $transaction->expires_at,
            ]);
        }
    }

    public function redeemPromotions(Transaction $transaction): void
    {
        $redemption = CouponRedemption::query()->where('transaction_id', $transaction->id)->lockForUpdate()->first();
        if ($redemption && $redemption->status === 'reserved') {
            $coupon = Coupon::query()->lockForUpdate()->find($redemption->coupon_id);
            if ($coupon) {
                $coupon->used_count = CouponRedemption::query()
                    ->where('coupon_id', $coupon->id)
                    ->where('status', 'redeemed')
                    ->count() + 1;
                $coupon->save();
            }
            $redemption->update(['status' => 'redeemed', 'redeemed_at' => now()]);
        }
    }

    public function commitInventory(Transaction $transaction, ?int $adminUserId = null): bool
    {
        $reservations = InventoryReservation::query()
            ->where('transaction_id', $transaction->id)
            ->orderBy('product_variant_id')
            ->lockForUpdate()
            ->get();

        if ($reservations->isEmpty()) {
            return false;
        }

        foreach ($reservations as $reservation) {
            if ($reservation->status === 'committed') {
                continue;
            }
            if ($reservation->status !== 'reserved') {
                throw ValidationException::withMessages(['stock' => 'Reservasi stok transaksi sudah tidak aktif.']);
            }

            $variant = ProductVariant::query()->lockForUpdate()->findOrFail($reservation->product_variant_id);
            $before = (int) $variant->stock;
            if ($before < (int) $reservation->quantity) {
                throw ValidationException::withMessages(['stock' => 'Stok fisik tidak mencukupi saat reservasi dikomit.']);
            }
            $after = $before - (int) $reservation->quantity;
            $variant->update(['stock' => $after]);

            $detailId = $transaction->details()->where('product_variant_id', $variant->id)->value('id');
            StockMovement::query()->create([
                'product_variant_id' => $variant->id,
                'transaction_detail_id' => $detailId,
                'admin_user_id' => $adminUserId,
                'type' => 'out',
                'quantity' => (int) $reservation->quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'source' => 'sales',
                'description' => 'Penjualan produk dari reservasi checkout',
            ]);
            $reservation->update(['status' => 'committed', 'committed_at' => now()]);
        }

        FlashSaleReservation::query()
            ->where('transaction_id', $transaction->id)
            ->where('status', 'reserved')
            ->orderBy('flash_sale_item_id')
            ->lockForUpdate()
            ->get()
            ->each(function (FlashSaleReservation $reservation): void {
                $item = FlashSaleItem::query()->lockForUpdate()->find($reservation->flash_sale_item_id);
                if ($item) {
                    $item->sold = min((int) $item->quota, (int) $item->sold + (int) $reservation->quantity);
                    $item->save();
                }
                $reservation->update(['status' => 'committed', 'committed_at' => now()]);
            });

        return true;
    }

    public function release(Transaction $transaction): void
    {
        InventoryReservation::query()->where('transaction_id', $transaction->id)->where('status', 'reserved')->update([
            'status' => 'released', 'released_at' => now(),
        ]);
        FlashSaleReservation::query()->where('transaction_id', $transaction->id)->where('status', 'reserved')->update([
            'status' => 'released', 'released_at' => now(),
        ]);
        CouponRedemption::query()->where('transaction_id', $transaction->id)->where('status', 'reserved')->update([
            'status' => 'released', 'released_at' => now(),
        ]);
    }
}
