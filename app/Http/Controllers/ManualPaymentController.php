<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceOrder;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionStatusHistory;
use App\Models\UserNotification;
use App\Services\CheckoutTaxCalculator;
use App\Services\ImageOptimizer;
use App\Services\LoyaltyPointService;
use App\Services\TaxInvoiceRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ManualPaymentController extends Controller
{
    public function checkout(Request $request, LoyaltyPointService $loyaltyPointService, CheckoutTaxCalculator $taxCalculator, TaxInvoiceRequestService $taxInvoiceService)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.productVariantId' => ['nullable', 'integer'],
            'items.*.companyId' => ['nullable', 'integer'],
            'items.*.name' => ['required', 'string'],
            'items.*.variant' => ['nullable', 'string'],
            'items.*.image' => ['nullable', 'string'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.redeemPoints' => ['nullable', 'integer', 'min:0'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_label' => ['nullable', 'string', 'max:100'],
            'address_id' => ['nullable', 'integer'],
            'tax_invoice' => ['nullable', 'array'],
            'tax_invoice.requested' => ['nullable', 'boolean'],
            'tax_invoice.profile_id' => ['nullable', 'integer'],
            'tax_invoice.taxpayer_name' => ['nullable', 'string', 'max:255'],
            'tax_invoice.taxpayer_number' => ['nullable', 'string', 'max:32'],
            'tax_invoice.taxpayer_address' => ['nullable', 'string', 'max:2000'],
            'tax_invoice.taxpayer_email' => ['nullable', 'string', 'max:255'],
            'tax_invoice.customer_note' => ['nullable', 'string', 'max:1000'],
            'tax_invoice.save_profile' => ['nullable', 'boolean'],
            'tax_invoice.set_default_profile' => ['nullable', 'boolean'],
        ]);

        $orderId = 'MAN-'.now()->format('YmdHis').'-'.random_int(1000, 9999);

        $transaction = DB::transaction(function () use ($request, $validated, $orderId, $loyaltyPointService, $taxCalculator, $taxInvoiceService) {
            $companyId = (int) $validated['company_id'];
            $items = collect($validated['items'])->values();

            $hasMixedCompanyItems = $items->contains(fn ($item) => !empty($item['companyId']) && (int) $item['companyId'] !== $companyId);
            if ($hasMixedCompanyItems) {
                abort(422, 'Item di keranjang berasal dari lebih dari satu perusahaan. Checkout lintas perusahaan belum didukung dalam satu transaksi.');
            }

            $subtotal = (int) $items->sum(fn ($item) => ((int) round((float) $item['price'])) * ((int) $item['qty']));
            $shippingCost = (int) round((float) $validated['shipping_cost']);
            $discountAmount = 0;
            $couponsByCompany = (array) session('checkout_coupon', []);
            $couponCode = (string) ($couponsByCompany[$companyId]['code'] ?? '');

            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->first();
                if (! $coupon || (int) $coupon->company_id !== $companyId || ! $coupon->isUsableFor($subtotal)) {
                    unset($couponsByCompany[$companyId]);
                    session(['checkout_coupon' => $couponsByCompany]);
                    abort(422, 'Voucher tidak valid atau sudah tidak bisa digunakan.');
                }
                $discountAmount = $coupon->discountFor($subtotal);
            }
            $tax = $taxCalculator->calculate($subtotal, $discountAmount, $shippingCost, companyId: $companyId);

            $snapshot = [];
            if (! empty($validated['address_id'])) {
                $addr = Address::query()
                    ->where('id', $validated['address_id'])
                    ->where('user_id', $request->user()?->id)
                    ->first();
                if ($addr) {
                    $snapshot = [
                        'shipping_recipient_name' => $addr->recipient_name,
                        'shipping_phone' => trim(($addr->phone_country_code ?? '').$addr->phone_number),
                        'shipping_address_line' => $addr->address_line,
                        'shipping_city' => $addr->city,
                        'shipping_province' => $addr->province,
                        'shipping_postal_code' => $addr->postal_code,
                    ];
                }
            }

            $transaction = Transaction::create([
                'company_id' => $companyId,
                'user_id' => $request->user()?->id,
                'source' => Transaction::SOURCE_CHECKOUT,
                'invoice_no' => $this->generateDailyInvoiceNo(),
                'order_id' => $orderId,
                'payment_type' => 'manual_transfer',
                'payment_method' => 'Transfer Manual',
                'status' => 'menunggu_verifikasi',
                'subtotal_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_code' => $couponCode,
                'discount_amount' => $discountAmount,
                'tax_name' => $tax['tax_name'],
                'tax_rate' => $tax['tax_rate'],
                'taxable_amount' => $tax['taxable_amount'],
                'tax_amount' => $tax['tax_amount'],
                'grand_total' => $tax['grand_total'],
                'shipping_label' => (string) ($validated['shipping_label'] ?? 'Reguler'),
                'shipping_recipient_name' => (string) ($snapshot['shipping_recipient_name'] ?? ''),
                'shipping_phone' => (string) ($snapshot['shipping_phone'] ?? ''),
                'shipping_address_line' => (string) ($snapshot['shipping_address_line'] ?? ''),
                'shipping_city' => (string) ($snapshot['shipping_city'] ?? ''),
                'shipping_province' => (string) ($snapshot['shipping_province'] ?? ''),
                'shipping_postal_code' => (string) ($snapshot['shipping_postal_code'] ?? ''),
                'expires_at' => now()->addDay(),
            ]);

            $detailRows = $items->map(function ($item) use ($transaction) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (int) round((float) ($item['price'] ?? 0));

                return [
                    'transaction_id' => $transaction->id,
                    'product_id' => isset($item['id']) ? (int) $item['id'] : null,
                    'product_variant_id' => isset($item['productVariantId']) ? (int) $item['productVariantId'] : null,
                    'product_name' => (string) ($item['name'] ?? '-'),
                    'variant_name' => (string) ($item['variant'] ?? ''),
                    'image' => (string) ($item['image'] ?? ''),
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $qty * $price,
                    'item_note' => ! empty($item['redeemPoints'])
                        ? LoyaltyPointService::buildRedeemItemNote((int) $item['redeemPoints'], (string) ($item['note'] ?? ''))
                        : (string) ($item['note'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            TransactionDetail::insert($detailRows);

            if ($discountAmount > 0 && $couponCode !== '') {
                Coupon::query()->where('code', $couponCode)->increment('used_count');
            }

            TransactionStatusHistory::create([
                'transaction_id' => $transaction->id,
                'user_id' => $request->user()?->id,
                'from_status' => null,
                'to_status' => 'menunggu_verifikasi',
                'type' => 'manual_checkout',
                'note' => 'Checkout dengan pembayaran transfer manual.',
            ]);

            $loyaltyPointService->reserveRedeemPoints($transaction);

            $taxInvoiceService->requestForTransaction($transaction, $request->user(), $validated['tax_invoice'] ?? []);

            return $transaction;
        });

        $checkout = session('checkout', []);
        if (($checkout['source'] ?? '') === 'cart_selected') {
            $ids = collect($checkout['cart_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
            if (! empty($ids)) {
                Cart::query()->where('user_id', $request->user()->id)->whereIn('id', $ids)->delete();
            }
        } elseif (! in_array(($checkout['source'] ?? ''), ['buy_now', 'redeem_point'], true)) {
            Cart::query()->where('user_id', $request->user()->id)->delete();
        }

        session()->forget(['checkout', 'checkout_coupon']);

        if ($request->user()?->email) {
            try {
                Mail::to($request->user()->email)->send(new InvoiceOrder($transaction->load('details', 'user')));
            } catch (\Throwable $e) {
                Log::warning('Invoice email failed after manual checkout.', [
                    'transaction_id' => $transaction->id,
                    'order_id' => $transaction->order_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        UserNotification::create([
            'user_id' => $request->user()->id,
            'type' => 'transaction_created',
            'title' => 'Pesanan Berhasil Dibuat',
            'body' => 'Upload bukti transfer untuk pesanan '.$transaction->invoice_no.' agar pembayaran bisa diverifikasi.',
            'url' => route('frontend.checkout.waiting', ['orderId' => $transaction->order_id]),
        ]);

        return response()->json([
            'ok' => true,
            'order_id' => $transaction->order_id,
            'transaction_db_id' => $transaction->id,
            'redirect_url' => route('frontend.checkout.waiting', ['orderId' => $transaction->order_id]),
        ]);
    }

    public function uploadProof(Request $request, Transaction $transaction, ImageOptimizer $imageOptimizer)
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $oldProof = (string) $transaction->payment_proof_path;
        $path = $imageOptimizer->storeWebp($validated['payment_proof'], 'payment-proofs', 1400, 1400, 82, true);
        $oldStatus = (string) $transaction->status;
        $transaction->update([
            'payment_proof_path' => $path,
            'payment_proof_uploaded_at' => now(),
            'payment_rejected_at' => null,
            'payment_admin_note' => null,
            'status' => 'menunggu_verifikasi',
        ]);

        $imageOptimizer->deletePublicFile($oldProof);

        TransactionStatusHistory::create([
            'transaction_id' => $transaction->id,
            'user_id' => $request->user()->id,
            'from_status' => $oldStatus,
            'to_status' => 'menunggu_verifikasi',
            'type' => 'payment_proof_uploaded',
            'note' => 'Customer mengupload bukti transfer.',
        ]);

        return back()->with('success', 'Bukti transfer berhasil diupload. Admin akan memverifikasi pembayaran.');
    }

    private function generateDailyInvoiceNo(): string
    {
        $prefix = 'INV-'.now()->format('YmdHis').'-';
        $sequence = ((int) Transaction::query()
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->lockForUpdate()
            ->count()) + 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
