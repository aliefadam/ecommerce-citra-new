<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\FlashSaleItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->buildCartItems(auth()->id());

        return view('frontend.cart', [
            'cartItems' => $cartItems,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::query()->firstOrNew([
            'user_id' => auth()->id(),
            'product_variant_id' => (int) $validated['product_variant_id'],
        ]);

        $cart->quantity = (int) ($cart->exists ? $cart->quantity : 0) + (int) $validated['quantity'];
        $cart->save();

        return response()->json([
            'ok' => true,
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'cartCount' => $this->countValue(auth()->id()),
        ]);
    }

    public function update(Request $request, Cart $cart)
    {
        abort_unless($cart->user_id === auth()->id(), 403);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart->update([
            'quantity' => (int) $validated['quantity'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Jumlah item berhasil diperbarui.',
            'cartCount' => $this->countValue(auth()->id()),
        ]);
    }

    public function destroy(Cart $cart)
    {
        abort_unless($cart->user_id === auth()->id(), 403);
        $cart->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Item berhasil dihapus.',
            'cartCount' => $this->countValue(auth()->id()),
        ]);
    }

    public function clear()
    {
        Cart::query()->where('user_id', auth()->id())->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Keranjang dikosongkan.',
            'cartCount' => 0,
        ]);
    }

    public function count()
    {
        return response()->json([
            'count' => $this->countValue(auth()->id()),
        ]);
    }

    public function items()
    {
        return response()->json([
            'items' => $this->buildCartItems(auth()->id()),
        ]);
    }

    public function prepareCheckout(Request $request)
    {
        $validated = $request->validate([
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['integer'],
        ]);

        $allowedIds = Cart::query()
            ->where('user_id', auth()->id())
            ->whereIn('id', $validated['cart_ids'])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        abort_if(empty($allowedIds), 422, 'Tidak ada item valid yang dipilih.');

        session([
            'checkout' => [
                'source' => 'cart_selected',
                'cart_ids' => $allowedIds,
            ],
        ]);

        return response()->json([
            'ok' => true,
            'redirect' => route('frontend.checkout'),
        ]);
    }

    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::query()
            ->with(['product', 'variant', 'flashSaleItems.flashSale'])
            ->findOrFail((int) $validated['product_variant_id']);

        $product = $variant->product;
        abort_unless($product && $product->status === 'active', 404);

        $basePrice = (int) $variant->price;
        $flashItem = $variant->flashSaleItems->first(function (FlashSaleItem $item) {
            $sale = $item->flashSale;
            if (!$sale || !$item->is_active || $sale->status !== 'active') {
                return false;
            }
            $now = now();
            return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
        });
        $salePrice = $flashItem ? (int) $flashItem->discount_price : $basePrice;
        $variantText = trim(
            ($variant->variant?->name ? $variant->variant->name . ': ' : '') .
            ($variant->variant?->value ?? '-')
        );

        session([
            'checkout' => [
                'source' => 'buy_now',
                'items' => [[
                    'cartId' => null,
                    'productVariantId' => (int) $variant->id,
                    'id' => (int) $product->id,
                    'slug' => (string) $product->slug,
                    'name' => (string) $product->name,
                    'variant' => $variantText !== '' ? $variantText : '-',
                    'price' => $salePrice,
                    'origPrice' => $basePrice,
                    'qty' => (int) $validated['quantity'],
                    'image' => $variant->image
                        ? (str_starts_with($variant->image, 'http://') || str_starts_with($variant->image, 'https://')
                            ? $variant->image
                            : asset('storage/' . ltrim($variant->image, '/')))
                        : 'https://via.placeholder.com/100x100?text=No+Image',
                    'isFlashSale' => (bool) $flashItem,
                ]],
            ],
        ]);

        return redirect()->route('frontend.checkout');
    }

    public function completeCheckout(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['nullable', 'string'],
        ]);

        $orderId = (string) ($validated['order_id'] ?? '');
        if ($orderId !== '') {
            $payment = session('checkout_waiting.' . $orderId, []);
            if (is_array($payment) && !empty($payment)) {
                $this->recordTransactionFromPayment($payment);
            }
        }

        $checkout = session('checkout', []);
        $source = (string) ($checkout['source'] ?? '');
        if ($source === 'cart_selected') {
            $ids = collect($checkout['cart_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->values()
                ->all();
            if (!empty($ids)) {
                Cart::query()
                    ->where('user_id', auth()->id())
                    ->whereIn('id', $ids)
                    ->delete();
            }
        }

        session()->forget('checkout');
        session()->forget('checkout_coupon');
        if ($orderId !== '') {
            session()->forget('checkout_waiting.' . $orderId);
        }

        return response()->json([
            'ok' => true,
            'cartCount' => $this->countValue(auth()->id()),
        ]);
    }

    private function countValue(int $userId): int
    {
        return (int) Cart::query()
            ->where('user_id', $userId)
            ->sum('quantity');
    }

    private function buildCartItems(int $userId): array
    {
        $rows = Cart::query()
            ->with(['productVariant.product', 'productVariant.variant', 'productVariant.flashSaleItems.flashSale'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return $rows->map(function (Cart $row) {
            $variant = $row->productVariant;
            $product = $variant?->product;
            if (!$variant || !$product) {
                return null;
            }

            $basePrice = (int) $variant->price;
            $flashItem = $variant->flashSaleItems->first(function (FlashSaleItem $item) {
                $sale = $item->flashSale;
                if (!$sale || !$item->is_active || $sale->status !== 'active') {
                    return false;
                }

                $now = now();
                return $sale->start_at && $sale->end_at && $now->between($sale->start_at, $sale->end_at);
            });

            $salePrice = $flashItem ? (int) $flashItem->discount_price : $basePrice;
            $variantText = trim(
                ($variant->variant?->name ? $variant->variant->name . ': ' : '') .
                ($variant->variant?->value ?? '-')
            );

            return [
                'cartId' => $row->id,
                'productVariantId' => $variant->id,
                'id' => $product->id,
                'slug' => (string) $product->slug,
                'name' => (string) $product->name,
                'variant' => $variantText !== '' ? $variantText : '-',
                'price' => $salePrice,
                'origPrice' => $basePrice,
                'qty' => (int) $row->quantity,
                'image' => $variant->image
                    ? (str_starts_with($variant->image, 'http://') || str_starts_with($variant->image, 'https://')
                        ? $variant->image
                        : asset('storage/' . ltrim($variant->image, '/')))
                    : 'https://via.placeholder.com/100x100?text=No+Image',
                'isFlashSale' => (bool) $flashItem,
            ];
        })->filter()->values()->all();
    }

    private function recordTransactionFromPayment(array $payment): void
    {
        $orderId = (string) ($payment['order_id'] ?? '');
        if ($orderId === '') {
            return;
        }

        DB::transaction(function () use ($payment, $orderId) {
            $existing = Transaction::query()->where('order_id', $orderId)->first();
            if ($existing) {
                return;
            }

            $items = collect($payment['items'] ?? [])->values();
            $subtotal = (int) $items->sum(function ($item) {
                return ((int) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0));
            });
            $shippingCost = (int) ($payment['shipping_cost'] ?? 0);
            $discountAmount = min($subtotal, max(0, (int) ($payment['discount_amount'] ?? 0)));
            $grandTotal = max(0, $subtotal + $shippingCost - $discountAmount);

            $transaction = Transaction::query()->create([
                'user_id' => auth()->id(),
                'invoice_no' => $this->generateDailyInvoiceNo(),
                'order_id' => $orderId,
                'midtrans_transaction_id' => (string) ($payment['transaction_id'] ?? ''),
                'payment_type' => (string) ($payment['payment_type'] ?? ''),
                'payment_method' => (string) ($payment['method_label'] ?? ''),
                'status' => (string) ($payment['transaction_status'] ?? 'pending'),
                'subtotal_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_code' => (string) ($payment['coupon_code'] ?? ''),
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'shipping_label' => (string) ($payment['shipping_label'] ?? ''),
                'paid_at' => now(),
                'expires_at' => !empty($payment['expires_at']) ? $payment['expires_at'] : null,
            ]);

            $detailRows = $items->map(function ($item) use ($transaction) {
                $qty = (int) ($item['qty'] ?? 0);
                $price = (int) ($item['price'] ?? 0);
                return [
                    'transaction_id' => $transaction->id,
                    'product_id' => isset($item['id']) ? (int) $item['id'] : null,
                    'product_variant_id' => isset($item['productVariantId']) ? (int) $item['productVariantId'] : null,
                    'product_name' => (string) ($item['name'] ?? '-'),
                    'variant_name' => (string) ($item['variant'] ?? ''),
                    'image' => (string) ($item['image'] ?? ''),
                    'price' => $price,
                    'quantity' => max(1, $qty),
                    'subtotal' => max(1, $qty) * $price,
                    'item_note' => (string) ($item['note'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (!empty($detailRows)) {
                TransactionDetail::query()->insert($detailRows);
            }

            if ($discountAmount > 0 && (string) ($payment['coupon_code'] ?? '') !== '') {
                Coupon::query()->where('code', (string) $payment['coupon_code'])->increment('used_count');
            }
        });
    }

    private function generateDailyInvoiceNo(): string
    {
        $todayPrefix = 'INV-' . now()->format('YmdHis') . '-';
        $dayStart = now()->startOfDay();
        $dayEnd = now()->endOfDay();

        $sequence = ((int) Transaction::query()
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->lockForUpdate()
            ->count()) + 1;

        return $todayPrefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
