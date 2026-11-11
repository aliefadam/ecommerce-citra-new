<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\CheckoutCouponController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ManualPaymentController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\RajaOngkirController;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CompanySetting;
use App\Models\Coupon;
use App\Models\Transaction;
use App\Services\CheckoutIdempotencyService;
use App\Services\CheckoutPricingService;
use App\Services\CheckoutTaxCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileCheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutPricingService $pricing,
        private readonly CheckoutTaxCalculator $taxes,
        private readonly CheckoutIdempotencyService $idempotency,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $groups = $this->checkoutGroups($request);

        return response()->json([
            'data' => [
                'groups' => $groups,
                'addresses' => $request->user()->addresses()
                    ->orderByDesc('is_primary')
                    ->latest()
                    ->get()
                    ->map(fn (Address $address) => $this->addressData($address)),
                'tax_profiles' => $request->user()->taxProfiles()
                    ->orderByDesc('is_default')
                    ->latest()
                    ->get()
                    ->map(fn ($profile) => [
                        'id' => (int) $profile->id,
                        'name' => (string) $profile->taxpayer_name,
                        'number' => (string) $profile->masked_taxpayer_number,
                        'email' => (string) $profile->taxpayer_email,
                        'is_default' => (bool) $profile->is_default,
                    ]),
                'point_balance' => (int) $request->user()->point_balance,
                'payment_methods' => ['manual', 'bca', 'bni', 'bri', 'mandiri', 'cimb', 'qris'],
            ],
        ]);
    }

    public function shippingOptions(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['integer'],
            'address_id' => ['required', 'integer'],
        ]);
        $address = Address::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($validated['address_id'])
            ->firstOrFail();
        if ((int) $address->destination_id < 1) {
            throw ValidationException::withMessages(['address_id' => 'Alamat belum memiliki tujuan pengiriman yang valid.']);
        }

        $group = $this->resolveGroup($request, (int) $validated['company_id'], $validated['cart_ids']);
        $request->merge([
            'destination_id' => (int) $address->destination_id,
            'company_id' => $group['company_id'],
            'items' => json_encode($group['request_items'], JSON_THROW_ON_ERROR),
        ]);

        return app()->call([app(RajaOngkirController::class), 'shippingOptions'], ['request' => $request]);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['integer'],
            'code' => ['required', 'string', 'max:50'],
        ]);
        $group = $this->resolveGroup($request, (int) $validated['company_id'], $validated['cart_ids']);
        $request->merge(['subtotal' => $group['subtotal']]);

        return app()->call([app(CheckoutCouponController::class), 'apply'], ['request' => $request]);
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        $request->validate(['company_id' => ['required', 'integer']]);

        return app()->call([app(CheckoutCouponController::class), 'remove'], ['request' => $request]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer'],
            'cart_ids' => ['required', 'array', 'min:1'],
            'cart_ids.*' => ['integer'],
            'address_id' => ['required', 'integer'],
            'shipping_quote_token' => ['required', 'string'],
            'checkout_version' => ['required', 'string', 'size:64'],
            'payment_method' => ['required', Rule::in(['manual', 'bca', 'bni', 'bri', 'mandiri', 'cimb', 'qris'])],
            'tax_invoice' => ['nullable', 'array'],
            'tax_invoice.requested' => ['nullable', 'boolean'],
            'tax_invoice.profile_id' => ['nullable', 'integer'],
            'tax_invoice.taxpayer_name' => ['nullable', 'string', 'max:255'],
            'tax_invoice.taxpayer_number' => ['nullable', 'string', 'max:32'],
            'tax_invoice.taxpayer_address' => ['nullable', 'string', 'max:2000'],
            'tax_invoice.taxpayer_email' => ['nullable', 'email', 'max:255'],
            'tax_invoice.customer_note' => ['nullable', 'string', 'max:1000'],
            'tax_invoice.save_profile' => ['nullable', 'boolean'],
            'tax_invoice.set_default_profile' => ['nullable', 'boolean'],
        ]);
        $key = trim((string) $request->header('Idempotency-Key'));
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $key)) {
            throw ValidationException::withMessages(['idempotency_key' => 'Header Idempotency-Key UUID wajib diisi.']);
        }

        $payloadHash = collect($validated)->except('checkout_version')->all();
        $replay = $this->idempotency->replay((int) $request->user()->id, $key, $payloadHash);
        if ($replay) {
            return $replay;
        }

        $group = $this->resolveGroup($request, (int) $validated['company_id'], $validated['cart_ids']);
        if (! hash_equals($group['checkout_version'], $validated['checkout_version'])) {
            throw ValidationException::withMessages([
                'checkout_version' => 'Harga, stok, atau isi keranjang berubah. Tinjau ulang checkout sebelum melanjutkan.',
            ]);
        }
        $address = Address::query()
            ->where('user_id', $request->user()->id)
            ->whereKey($validated['address_id'])
            ->firstOrFail();

        return $this->idempotency->execute((int) $request->user()->id, $key, $payloadHash, function () use ($request, $validated, $group, $address) {
            $coupons = (array) session('checkout_coupon', []);
            session([
                'checkout' => [
                    'source' => 'cart_selected',
                    'cart_ids' => $group['cart_ids'],
                ],
            ]);
            $request->merge([
                'items' => $group['request_items'],
                'company_id' => $group['company_id'],
                'address_id' => (int) $address->id,
                'shipping_cost' => 0,
                'shipping_label' => '',
                'shipping_quote_token' => $validated['shipping_quote_token'],
                'tax_invoice' => $validated['tax_invoice'] ?? [],
            ]);

            if ($validated['payment_method'] === 'manual') {
                $response = app()->call([app(ManualPaymentController::class), 'checkout'], ['request' => $request]);
            } else {
                $request->merge(['payment_method' => $validated['payment_method']]);
                $response = app()->call([app(MidtransController::class), 'createCharge'], ['request' => $request]);
            }

            if ($response->isSuccessful()) {
                Cart::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('id', $group['cart_ids'])
                    ->delete();
                unset($coupons[$group['company_id']]);
                session(['checkout_coupon' => $coupons]);
            }

            return $response;
        });
    }

    public function payment(Request $request, string $orderId): JsonResponse
    {
        $transaction = $this->ownedTransaction($request, $orderId);

        return response()->json(['data' => $this->paymentData($transaction)]);
    }

    public function paymentStatus(Request $request, string $orderId): JsonResponse
    {
        $transaction = $this->ownedTransaction($request, $orderId);
        if ((string) $transaction->payment_type === 'manual_transfer') {
            return response()->json(['data' => $this->paymentData($transaction->fresh())]);
        }

        $providerResponse = app()->call([app(MidtransController::class), 'status'], [
            'request' => $request,
            'orderId' => $orderId,
        ]);
        if (! $providerResponse->isSuccessful()) {
            return $providerResponse;
        }

        return response()->json(['data' => $this->paymentData($transaction->fresh())]);
    }

    public function cancel(Request $request, string $orderId): JsonResponse
    {
        $this->ownedTransaction($request, $orderId);

        return app()->call([app(MidtransController::class), 'cancel'], [
            'request' => $request,
            'orderId' => $orderId,
        ]);
    }

    public function storeAddress(Request $request): JsonResponse
    {
        $validated = $this->validateAddress($request);
        $address = DB::transaction(function () use ($request, $validated) {
            if (! empty($validated['is_primary'])) {
                $request->user()->addresses()->update(['is_primary' => false]);
            }
            $address = $request->user()->addresses()->create($validated);
            if (! $request->user()->addresses()->where('is_primary', true)->exists()) {
                $address->update(['is_primary' => true]);
            }

            return $address;
        });

        return response()->json(['data' => $this->addressData($address)], 201);
    }

    public function provinces(): JsonResponse
    {
        return app()->call([app(RajaOngkirController::class), 'provinces']);
    }

    public function cities(Request $request): JsonResponse
    {
        return app()->call([app(RajaOngkirController::class), 'cities'], ['request' => $request]);
    }

    public function districts(Request $request): JsonResponse
    {
        return app()->call([app(RajaOngkirController::class), 'districts'], ['request' => $request]);
    }

    public function subdistricts(Request $request): JsonResponse
    {
        return app()->call([app(RajaOngkirController::class), 'subdistricts'], ['request' => $request]);
    }

    public function uploadProof(Request $request, string $orderId): JsonResponse
    {
        $transaction = $this->ownedTransaction($request, $orderId);

        app()->call([app(ManualPaymentController::class), 'uploadProof'], [
            'request' => $request,
            'transaction' => $transaction,
        ]);

        return response()->json(['data' => $this->paymentData($transaction->fresh())]);
    }

    private function checkoutGroups(Request $request): array
    {
        $rows = Cart::query()
            ->with('productVariant.product.company')
            ->where('user_id', $request->user()->id)
            ->get()
            ->filter(fn (Cart $cart) => $cart->productVariant?->product)
            ->groupBy(fn (Cart $cart) => (int) $cart->productVariant->product->company_id);

        return $rows->map(fn ($companyRows, $companyId) => $this->resolveGroup(
            $request,
            (int) $companyId,
            $companyRows->pluck('id')->all(),
        ))->values()->all();
    }

    private function resolveGroup(Request $request, int $companyId, array $cartIds): array
    {
        $rows = Cart::query()
            ->with('productVariant.product.company')
            ->where('user_id', $request->user()->id)
            ->whereIn('id', collect($cartIds)->map(fn ($id) => (int) $id)->all())
            ->get();
        if ($rows->isEmpty() || $rows->contains(fn (Cart $cart) => (int) $cart->productVariant?->product?->company_id !== $companyId)) {
            throw ValidationException::withMessages(['cart_ids' => 'Item checkout tidak valid atau berasal dari perusahaan berbeda.']);
        }
        if ($rows->contains(fn (Cart $cart) => ! $cart->productVariant?->product?->company?->is_active)) {
            throw ValidationException::withMessages(['cart_ids' => 'Salah satu toko sudah tidak aktif.']);
        }

        $requestItems = $rows->map(fn (Cart $cart) => [
            'productVariantId' => (int) $cart->product_variant_id,
            'qty' => (int) $cart->quantity,
        ])->all();
        $pricing = $this->pricing->resolve($requestItems, $companyId);
        $couponCode = (string) data_get(session('checkout_coupon', []), $companyId.'.code', '');
        $coupon = $couponCode !== '' ? Coupon::query()->where('company_id', $companyId)->where('normalized_code', Coupon::normalizeCode($couponCode))->first() : null;
        $discount = $coupon && $coupon->isUsableFor($pricing['subtotal']) ? $coupon->discountFor($pricing['subtotal']) : 0;
        $tax = $this->taxes->calculate($pricing['subtotal'], $discount, 0, companyId: $companyId);
        $version = hash('sha256', json_encode([
            'company_id' => $companyId,
            'items' => collect($pricing['items'])->map(fn ($item) => [
                'variant_id' => $item['productVariantId'],
                'quantity' => $item['qty'],
                'price' => $item['price'],
            ])->values()->all(),
            'coupon' => $couponCode,
            'discount' => $discount,
            'tax' => $tax,
        ], JSON_THROW_ON_ERROR));

        return [
            'company_id' => $companyId,
            'company_name' => (string) $rows->first()->productVariant->product->company->name,
            'cart_ids' => $rows->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'request_items' => $requestItems,
            'items' => $pricing['items'],
            'subtotal' => $pricing['subtotal'],
            'coupon_code' => $couponCode,
            'discount_amount' => $discount,
            'tax_name' => $tax['tax_name'],
            'tax_rate' => $tax['tax_rate'],
            'tax_amount' => $tax['tax_amount'],
            'total_before_shipping' => $tax['grand_total'],
            'checkout_version' => $version,
        ];
    }

    private function ownedTransaction(Request $request, string $orderId): Transaction
    {
        return Transaction::query()
            ->with('company')
            ->where('user_id', $request->user()->id)
            ->where('order_id', $orderId)
            ->firstOrFail();
    }

    private function paymentData(Transaction $transaction): array
    {
        return [
            'order_id' => (string) $transaction->order_id,
            'company' => (string) ($transaction->company?->name ?? ''),
            'status' => (string) $transaction->status,
            'payment_type' => (string) $transaction->payment_type,
            'payment_method' => (string) $transaction->payment_method,
            'grand_total' => (int) $transaction->grand_total,
            'va_number' => (string) ($transaction->payment_va_number ?? ''),
            'va_bank' => (string) ($transaction->payment_va_bank ?? ''),
            'qr_url' => (string) ($transaction->payment_qr_url ?? ''),
            'expires_at' => $transaction->expires_at?->toIso8601String(),
            'proof_uploaded' => filled($transaction->payment_proof_path),
            'manual_payment' => (string) $transaction->payment_type === 'manual_transfer'
                ? CompanySetting::manualPayment((int) $transaction->company_id)
                : null,
        ];
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', Rule::in(['Rumah', 'Kantor', 'Lainnya'])],
            'recipient_name' => ['required', 'string', 'max:100'],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone_number' => ['required', 'string', 'max:30'],
            'province_id' => ['nullable', 'integer'],
            'city_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'subdistrict_id' => ['nullable', 'integer'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'subdistrict' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:12'],
            'destination_id' => ['required', 'integer', 'min:1'],
            'address_line' => ['required', 'string', 'max:1000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }

    private function addressData(Address $address): array
    {
        return [
            'id' => (int) $address->id,
            'label' => (string) $address->label,
            'recipient_name' => (string) $address->recipient_name,
            'phone' => trim((string) $address->phone_country_code.' '.(string) $address->phone_number),
            'province' => (string) $address->province,
            'city' => (string) $address->city,
            'district' => (string) $address->district,
            'postal_code' => (string) $address->postal_code,
            'address_line' => (string) $address->address_line,
            'destination_id' => (int) $address->destination_id,
            'is_primary' => (bool) $address->is_primary,
        ];
    }
}
