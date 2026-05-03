<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MidtransController extends Controller
{
    public function createCharge(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required'],
            'items.*.productVariantId' => ['nullable', 'integer'],
            'items.*.name' => ['required', 'string'],
            'items.*.variant' => ['nullable', 'string'],
            'items.*.image' => ['nullable', 'string'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'shipping_label' => ['nullable', 'string', 'max:100'],
            'address_id' => ['nullable', 'integer'],
            'payment_method' => ['required', 'string', 'in:bca,bni,bri,mandiri,cimb,qris'],
        ]);

        try {
            $serverKey = (string) env('MIDTRANS_SERVER_KEY', '');
            if ($serverKey === '') {
                throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
            }

            $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
            $baseUrl = $isProduction
                ? 'https://api.midtrans.com/v2/charge'
                : 'https://api.sandbox.midtrans.com/v2/charge';

            $itemDetails = collect($validated['items'])->map(function ($item) {
                return [
                    'id' => (string) $item['id'],
                    'price' => (int) round((float) $item['price']),
                    'quantity' => (int) $item['qty'],
                    'name' => mb_substr((string) $item['name'], 0, 50),
                ];
            })->values()->all();

            $shippingCost = (int) round((float) $validated['shipping_cost']);
            if ($shippingCost > 0) {
                $itemDetails[] = [
                    'id' => 'SHIPPING',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => mb_substr('Ongkos Kirim - ' . (string) ($validated['shipping_label'] ?? 'Reguler'), 0, 50),
                ];
            }

            $grossAmount = collect($itemDetails)->sum(fn ($i) => ((int) $i['price']) * ((int) $i['quantity']));
            $orderId = 'ORD-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);

            $customer = $request->user();
            $payload = [
                'payment_type' => $validated['payment_method'] === 'qris' ? 'qris' : 'bank_transfer',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
                'custom_expiry' => [
                    'expiry_duration' => 30,
                    'unit' => 'minute',
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => (string) ($customer->first_name ?: $customer->name ?: 'Customer'),
                    'last_name' => (string) ($customer->last_name ?? ''),
                    'email' => (string) ($customer->email ?? 'customer@example.com'),
                    'phone' => trim(((string) ($customer->phone_country_code ?? '+62')) . ((string) ($customer->phone_number ?? ''))),
                ],
            ];

            if ($validated['payment_method'] === 'qris') {
                $payload['qris'] = [
                    'acquirer' => 'gopay',
                ];
            } else {
                $payload['bank_transfer'] = [
                    'bank' => $validated['payment_method'],
                ];
            }

            $auth = base64_encode($serverKey . ':');
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Basic ' . $auth,
                ])
                ->post($baseUrl, $payload);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal membuat transaksi Midtrans: ' . $response->body());
            }

            $json = $response->json();
            $transactionStatus = (string) ($json['transaction_status'] ?? 'pending');
            $expiryTime = (string) ($json['expiry_time'] ?? '');
            $paymentType = (string) ($json['payment_type'] ?? $payload['payment_type']);

            $vaNumber = null;
            $vaBank = null;
            if (isset($json['va_numbers']) && is_array($json['va_numbers']) && count($json['va_numbers']) > 0) {
                $vaNumber = $json['va_numbers'][0]['va_number'] ?? null;
                $vaBank = $json['va_numbers'][0]['bank'] ?? null;
            } elseif (isset($json['permata_va_number'])) {
                $vaNumber = (string) $json['permata_va_number'];
                $vaBank = 'permata';
            } elseif (isset($json['bill_key']) || isset($json['biller_code'])) {
                $vaNumber = trim(((string) ($json['biller_code'] ?? '')) . ' ' . ((string) ($json['bill_key'] ?? '')));
                $vaBank = 'mandiri';
            }

            $qrActions = collect($json['actions'] ?? [])->values();
            $qrUrl = null;
            foreach ($qrActions as $action) {
                if (($action['name'] ?? '') === 'generate-qr-code') {
                    $qrUrl = (string) ($action['url'] ?? null);
                    break;
                }
            }
            if ($qrUrl === null) {
                foreach ($qrActions as $action) {
                    if (Str::contains((string) ($action['url'] ?? ''), 'qr')) {
                        $qrUrl = (string) ($action['url'] ?? null);
                        break;
                    }
                }
            }

            $displayItems = collect($validated['items'])->map(function ($item) {
                $rawImage = (string) ($item['image'] ?? '');
                $image = $rawImage;

                if ($image !== '' && !Str::startsWith($image, ['http://', 'https://', '//', 'data:'])) {
                    $image = asset(ltrim($image, '/'));
                }

                if ($image === '') {
                    $image = 'https://via.placeholder.com/80x80?text=No+Image';
                }

                return [
                    'id' => $item['id'],
                    'productVariantId' => $item['productVariantId'] ?? null,
                    'name' => $item['name'],
                    'variant' => $item['variant'] ?? '',
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'image' => $image,
                    'note' => (string) ($item['note'] ?? ''),
                ];
            })->values()->all();

            $addressSnapshot = [];
            if (!empty($validated['address_id'])) {
                $addr = Address::query()
                    ->where('id', $validated['address_id'])
                    ->where('user_id', $request->user()?->id)
                    ->first();
                if ($addr) {
                    $addressSnapshot = [
                        'shipping_recipient_name' => $addr->recipient_name,
                        'shipping_phone' => trim(($addr->phone_country_code ?? '') . $addr->phone_number),
                        'shipping_address_line' => $addr->address_line,
                        'shipping_city' => $addr->city,
                        'shipping_province' => $addr->province,
                        'shipping_postal_code' => $addr->postal_code,
                    ];
                }
            }

            $paymentSummary = [
                'order_id' => $orderId,
                'transaction_id' => (string) ($json['transaction_id'] ?? ''),
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'gross_amount' => $grossAmount,
                'shipping_cost' => $shippingCost,
                'shipping_label' => (string) ($validated['shipping_label'] ?? 'Reguler'),
                'items' => $displayItems,
                'method_label' => strtoupper($validated['payment_method']),
                'va_number' => $vaNumber,
                'va_bank' => $vaBank,
                'qr_url' => $qrUrl,
                'expiry_time' => $expiryTime,
                'created_at' => now()->toIso8601String(),
                'expires_at' => now()->addMinutes(30)->toIso8601String(),
                'address_snapshot' => $addressSnapshot,
            ];

            session()->put('checkout_waiting.' . $orderId, $paymentSummary);
            $this->upsertTransactionFromPayment($request, $paymentSummary);

            return response()->json([
                'order_id' => $orderId,
                'redirect_url' => route('frontend.checkout.waiting', ['orderId' => $orderId]),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function waiting(Request $request, string $orderId)
    {
        $data = session('checkout_waiting.' . $orderId);
        abort_if(!$data, 404);

        if ($this->isExpired($data)) {
            $this->cancelMidtransTransaction($orderId);
            $data['transaction_status'] = 'expire';
            session()->put('checkout_waiting.' . $orderId, $data);
        }

        return view('frontend.checkout-waiting', [
            'payment' => $data,
        ]);
    }

    public function status(Request $request, string $orderId)
    {
        try {
            $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
            if (!$isProduction) {
                $localTx = Transaction::query()
                    ->where('order_id', $orderId)
                    ->where('user_id', $request->user()?->id)
                    ->first();
                // Short-circuit untuk status yang tidak perlu dicek ke Midtrans:
                // paid/settlement/capture = sudah lunas, process/kirim = sudah dikelola admin
                $localFinalStatuses = ['settlement', 'capture', 'paid', 'process', 'kirim'];
                if ($localTx && in_array(strtolower((string) $localTx->status), $localFinalStatuses, true)) {
                    return response()->json([
                        'transaction_status' => (string) $localTx->status,
                        'payment_type' => (string) ($localTx->payment_type ?? ''),
                        'order_id' => $orderId,
                    ]);
                }
            }

            $sessionData = session('checkout_waiting.' . $orderId);
            if (is_array($sessionData) && $this->isExpired($sessionData)) {
                $this->cancelMidtransTransaction($orderId);
                $sessionData['transaction_status'] = 'expire';
                session()->put('checkout_waiting.' . $orderId, $sessionData);

                return response()->json([
                    'transaction_status' => 'expire',
                    'payment_type' => (string) ($sessionData['payment_type'] ?? ''),
                    'order_id' => $orderId,
                ]);
            }

            $serverKey = (string) env('MIDTRANS_SERVER_KEY', '');
            if ($serverKey === '') {
                throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
            }

            $baseUrl = $isProduction
                ? 'https://api.midtrans.com/v2/' . $orderId . '/status'
                : 'https://api.sandbox.midtrans.com/v2/' . $orderId . '/status';

            $auth = base64_encode($serverKey . ':');
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $auth,
                ])->get($baseUrl);

            if (!$response->successful()) {
                throw new RuntimeException('Gagal cek status Midtrans.');
            }

            $json = $response->json();
            $status = (string) ($json['transaction_status'] ?? 'pending');
            $this->syncTransactionStatus($orderId, $status);

            return response()->json([
                'transaction_status' => $status,
                'payment_type' => (string) ($json['payment_type'] ?? ''),
                'order_id' => (string) ($json['order_id'] ?? $orderId),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request, string $orderId)
    {
        try {
            $this->cancelMidtransTransaction($orderId);
            $sessionData = session('checkout_waiting.' . $orderId);
            if (is_array($sessionData)) {
                $sessionData['transaction_status'] = 'cancel';
                session()->put('checkout_waiting.' . $orderId, $sessionData);
            }

            return response()->json([
                'ok' => true,
                'transaction_status' => 'cancel',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function simulate(Request $request)
    {
        $validated = $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        try {
            $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
            if ($isProduction) {
                throw new RuntimeException('Simulasi hanya tersedia di mode sandbox.');
            }

            $orderId = (string) $validated['order_id'];
            $tx = Transaction::query()
                ->where('order_id', $orderId)
                ->where('user_id', $request->user()?->id)
                ->first();

            if (!$tx) {
                throw new RuntimeException('Order ID tidak ditemukan.');
            }

            // Validasi order memang ada di Midtrans
            $serverKey = (string) env('MIDTRANS_SERVER_KEY', '');
            if ($serverKey === '') {
                throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
            }
            $auth = base64_encode($serverKey . ':');
            $statusRes = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $auth,
                ])->get('https://api.sandbox.midtrans.com/v2/' . $orderId . '/status');

            if (!$statusRes->successful()) {
                throw new RuntimeException('Order ID tidak ditemukan di Midtrans sandbox.');
            }

            $tx->status = 'settlement';
            $tx->paid_at = now();
            $tx->save();

            $sessionPayment = session('checkout_waiting.' . $orderId, []);
            if (is_array($sessionPayment) && !empty($sessionPayment)) {
                $sessionPayment['transaction_status'] = 'settlement';
                session()->put('checkout_waiting.' . $orderId, $sessionPayment);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Simulasi pembayaran berhasil.',
                'transaction_status' => 'settlement',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function cancelMidtransTransaction(string $orderId): void
    {
        $serverKey = (string) env('MIDTRANS_SERVER_KEY', '');
        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum dikonfigurasi.');
        }

        $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        $url = $isProduction
            ? 'https://api.midtrans.com/v2/' . $orderId . '/cancel'
            : 'https://api.sandbox.midtrans.com/v2/' . $orderId . '/cancel';

        $auth = base64_encode($serverKey . ':');
        Http::timeout(30)
            ->withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $auth,
            ])->post($url);
    }

    private function isExpired(array $payment): bool
    {
        $expiresAt = $payment['expires_at'] ?? null;
        if (!$expiresAt) {
            return false;
        }

        return Carbon::now()->greaterThanOrEqualTo(Carbon::parse($expiresAt));
    }

    private function upsertTransactionFromPayment(Request $request, array $payment): void
    {
        $orderId = (string) ($payment['order_id'] ?? '');
        if ($orderId === '') {
            return;
        }

        DB::transaction(function () use ($request, $payment, $orderId) {
            $items = collect($payment['items'] ?? [])->values();
            $subtotal = (int) $items->sum(function ($item) {
                return ((int) ($item['price'] ?? 0)) * ((int) ($item['qty'] ?? 0));
            });
            $shippingCost = (int) ($payment['shipping_cost'] ?? 0);
            $grandTotal = $subtotal + $shippingCost;

            $transaction = Transaction::query()->firstOrNew(['order_id' => $orderId]);
            if (!$transaction->exists) {
                $transaction->invoice_no = $this->generateDailyInvoiceNo();
            }

            $snapshot = $payment['address_snapshot'] ?? [];
            $transaction->fill([
                'user_id' => $request->user()?->id,
                'midtrans_transaction_id' => (string) ($payment['transaction_id'] ?? ''),
                'payment_type' => (string) ($payment['payment_type'] ?? ''),
                'payment_method' => (string) ($payment['method_label'] ?? ''),
                'status' => (string) ($payment['transaction_status'] ?? 'pending'),
                'subtotal_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'shipping_label' => (string) ($payment['shipping_label'] ?? ''),
                'shipping_recipient_name' => (string) ($snapshot['shipping_recipient_name'] ?? ''),
                'shipping_phone' => (string) ($snapshot['shipping_phone'] ?? ''),
                'shipping_address_line' => (string) ($snapshot['shipping_address_line'] ?? ''),
                'shipping_city' => (string) ($snapshot['shipping_city'] ?? ''),
                'shipping_province' => (string) ($snapshot['shipping_province'] ?? ''),
                'shipping_postal_code' => (string) ($snapshot['shipping_postal_code'] ?? ''),
                'expires_at' => !empty($payment['expires_at']) ? $payment['expires_at'] : null,
            ]);
            $transaction->save();

            $detailRows = $items->map(function ($item) use ($transaction) {
                $qty = max(1, (int) ($item['qty'] ?? 1));
                $price = (int) ($item['price'] ?? 0);
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
                    'item_note' => (string) ($item['note'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (!empty($detailRows)) {
                TransactionDetail::query()->where('transaction_id', $transaction->id)->delete();
                TransactionDetail::query()->insert($detailRows);
            }
        });
    }

    private function syncTransactionStatus(string $orderId, string $status): void
    {
        $tx = Transaction::query()->where('order_id', $orderId)->first();
        if (!$tx) {
            return;
        }

        // Jangan overwrite status yang sudah dikelola admin (process/kirim)
        $adminManagedStatuses = ['process', 'kirim'];
        if (in_array(strtolower((string) $tx->status), $adminManagedStatuses, true)) {
            return;
        }

        $tx->status = $status;
        if (in_array(strtolower($status), ['settlement', 'capture', 'paid'], true) && !$tx->paid_at) {
            $tx->paid_at = now();
        }
        $tx->save();
    }

    private function generateDailyInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('YmdHis') . '-';
        $dayStart = now()->startOfDay();
        $dayEnd = now()->endOfDay();
        $sequence = ((int) Transaction::query()
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->lockForUpdate()
            ->count()) + 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
