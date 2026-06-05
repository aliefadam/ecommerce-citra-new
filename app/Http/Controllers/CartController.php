<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\FlashSaleItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\TransactionStatusHistory;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $variant = ProductVariant::query()
            ->with('product')
            ->findOrFail((int) $validated['product_variant_id']);
        $this->ensureVariantCanBePurchased($variant, (int) $validated['quantity']);

        $cart = Cart::query()->firstOrNew([
            'user_id' => auth()->id(),
            'product_variant_id' => (int) $validated['product_variant_id'],
        ]);

        $nextQuantity = (int) ($cart->exists ? $cart->quantity : 0) + (int) $validated['quantity'];
        $this->ensureVariantCanBePurchased($variant, $nextQuantity);
        $cart->quantity = $nextQuantity;
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

        $cart->loadMissing('productVariant.product');
        $this->ensureCartItemCanBePurchased($cart, (int) $validated['quantity']);

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

        $cartRows = Cart::query()
            ->with('productVariant.product')
            ->where('user_id', auth()->id())
            ->whereIn('id', $validated['cart_ids'])
            ->get();

        $allowedIds = $cartRows
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        abort_if(empty($allowedIds), 422, 'Tidak ada item valid yang dipilih.');

        foreach ($cartRows as $cartRow) {
            $this->ensureCartItemCanBePurchased($cartRow, (int) $cartRow->quantity);
        }

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
            ->with(['product.productVariants', 'variant', 'attributeValues.definition', 'flashSaleItems.flashSale'])
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
        $variantText = $variant->attributeSummary();

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
                    'image' => $this->resolveVariantImageUrl($variant),
                    'isFlashSale' => (bool) $flashItem,
                ]],
            ],
        ]);

        return redirect()->route('frontend.checkout');
    }

    public function prepareRedeemCheckout(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::query()
            ->with(['product', 'variant', 'attributeValues.definition'])
            ->findOrFail((int) $validated['product_variant_id']);

        $item = $this->buildRedeemCheckoutItem($variant, (int) $validated['quantity']);
        $this->ensureRedeemItemCanBeProcessed($request->user()->id, $item);

        session([
            'checkout' => [
                'source' => 'redeem_point',
                'items' => [$item],
            ],
        ]);
        session()->forget('checkout_coupon');

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
            ->with(['productVariant.product.productVariants', 'productVariant.variant', 'productVariant.attributeValues.definition', 'productVariant.flashSaleItems.flashSale'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return $rows->map(function (Cart $row) {
            $variant = $row->productVariant;
            $product = $variant?->product;
            if (!$variant || !$product || $product->status !== 'active' || (int) $variant->stock < 1) {
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
            $variantText = $variant->attributeSummary();

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
                'stock' => (int) $variant->stock,
                'image' => $this->resolveVariantImageUrl($variant),
                'isFlashSale' => (bool) $flashItem,
            ];
        })->filter()->values()->all();
    }

    private function resolveVariantImageUrl(ProductVariant $variant): string
    {
        $image = trim((string) ($variant->image ?: $variant->product?->firstAvailableImagePath() ?: ''));

        if ($image === '') {
            return 'https://via.placeholder.com/100x100?text=No+Image';
        }

        if (
            str_starts_with($image, 'http://') ||
            str_starts_with($image, 'https://') ||
            str_starts_with($image, '//') ||
            str_starts_with($image, 'data:')
        ) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
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
                'source' => Transaction::SOURCE_CHECKOUT,
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

    private function ensureCartItemCanBePurchased(Cart $cart, int $requestedQuantity): void
    {
        $variant = $cart->productVariant;
        abort_unless($variant instanceof ProductVariant, 404);

        $variant->loadMissing('product');
        $this->ensureVariantCanBePurchased($variant, $requestedQuantity);
    }

    private function ensureVariantCanBePurchased(ProductVariant $variant, int $requestedQuantity): void
    {
        $product = $variant->product;

        if (!$product || $product->status !== 'active') {
            $this->abortJson(422, 'Produk ini sudah tidak tersedia.');
        }

        $stock = max(0, (int) $variant->stock);
        if ($stock < 1) {
            $this->abortJson(422, 'Stok produk ini sedang habis.');
        }

        if ($requestedQuantity > $stock) {
            $this->abortJson(422, 'Jumlah melebihi stok yang tersedia. Stok saat ini: ' . $stock . '.');
        }
    }

    private function buildRedeemCheckoutItem(ProductVariant $variant, int $quantity, string $note = ''): array
    {
        $product = $variant->product;
        abort_unless($product && $product->status === 'active', 404);

        if (!(bool) $product->is_redeem_product || (int) ($product->redeem_points ?? 0) < 1) {
            $this->abortJson(422, 'Produk ini belum tersedia untuk redeem point.');
        }

        $this->ensureVariantCanBePurchased($variant, $quantity);

        $variantText = $variant->attributeSummary();

        return [
            'cartId' => null,
            'productVariantId' => (int) $variant->id,
            'id' => (int) $product->id,
            'slug' => (string) $product->slug,
            'name' => (string) $product->name,
            'variant' => $variantText !== '' ? $variantText : '-',
            'price' => 0,
            'origPrice' => 0,
            'qty' => $quantity,
            'image' => $this->resolveVariantImageUrl($variant),
            'isFlashSale' => false,
            'isRedeemProduct' => true,
            'redeemPoints' => (int) ($product->redeem_points ?? 0),
            'note' => $note,
        ];
    }

    private function ensureRedeemItemCanBeProcessed(int $userId, array $item, ?int $knownBalance = null): void
    {
        $requiredPoints = ((int) ($item['redeemPoints'] ?? 0)) * ((int) ($item['qty'] ?? 0));
        if ($requiredPoints < 1) {
            $this->abortJson(422, 'Produk redeem tidak memiliki nilai point yang valid.');
        }

        $pointBalance = $knownBalance;
        if ($pointBalance === null) {
            $pointBalance = (int) optional(auth()->user())->point_balance;
            if ((int) optional(auth()->user())->id !== $userId) {
                $pointBalance = (int) \App\Models\User::query()->where('id', $userId)->value('point_balance');
            }
        }

        if ($pointBalance < $requiredPoints) {
            $this->abortJson(422, 'Point kamu tidak cukup. Dibutuhkan ' . number_format($requiredPoints, 0, ',', '.') . ' point.');
        }
    }

    private function abortJson(int $status, string $message): never
    {
        if (!request()->expectsJson()) {
            throw ValidationException::withMessages([
                'checkout' => $message,
            ]);
        }

        throw new HttpResponseException(response()->json([
            'message' => $message,
        ], $status));
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
