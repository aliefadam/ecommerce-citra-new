<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceOrder;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionStatusHistory;
use App\Models\StoreSetting;
use App\Models\UserNotification;
use App\Services\LoyaltyPointService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
            'items.*.redeemPoints' => ['nullable', 'integer', 'min:0'],
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
            $couponData = session('checkout_coupon', []);
            $couponCode = (string) ($couponData['code'] ?? '');
            $discountAmount = 0;

            if ($couponCode !== '') {
                $coupon = Coupon::query()->where('code', $couponCode)->first();
                $subtotal = collect($validated['items'])->sum(fn($item) => ((int) round((float) $item['price'])) * ((int) $item['qty']));
                if ($coupon && $coupon->isUsableFor((int) $subtotal)) {
                    $discountAmount = $coupon->discountFor((int) $subtotal);
                } else {
                    session()->forget('checkout_coupon');
                    throw new RuntimeException('Voucher tidak valid atau sudah tidak bisa digunakan.');
                }
            }

            if ($shippingCost > 0) {
                $itemDetails[] = [
                    'id' => 'SHIPPING',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => mb_substr('Ongkos Kirim - ' . (string) ($validated['shipping_label'] ?? 'Reguler'), 0, 50),
                ];
            }
            if ($discountAmount > 0) {
                $itemDetails[] = [
                    'id' => 'DISCOUNT',
                    'price' => -$discountAmount,
                    'quantity' => 1,
                    'name' => mb_substr('Voucher ' . $couponCode, 0, 50),
                ];
            }

            $grossAmount = collect($itemDetails)->sum(fn($i) => ((int) $i['price']) * ((int) $i['quantity']));
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
                'coupon_code' => $couponCode,
                'discount_amount' => $discountAmount,
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
        $tx = Transaction::query()
            ->with('details', 'user')
            ->where('order_id', $orderId)
            ->where('user_id', $request->user()?->id)
            ->first();

        if (!$data) {
            abort_if(!$tx, 404);

            $data = [
                'order_id'           => (string) $tx->order_id,
                'transaction_db_id'  => (int) $tx->id,
                'transaction_id'     => (string) ($tx->midtrans_transaction_id ?? ''),
                'transaction_status' => (string) ($tx->status ?? 'pending'),
                'payment_type'       => (string) ($tx->payment_type ?? ''),
                'method_label'       => (string) ($tx->payment_method ?? '-'),
                'gross_amount'       => (int) $tx->grand_total,
                'va_number'          => (string) ($tx->payment_va_number ?? ''),
                'va_bank'            => (string) ($tx->payment_va_bank ?? ''),
                'qr_url'             => (string) ($tx->payment_qr_url ?? ''),
                'shipping_cost'      => (int) $tx->shipping_cost,
                'discount_amount'    => (int) ($tx->discount_amount ?? 0),
                'coupon_code'         => (string) ($tx->coupon_code ?? ''),
                'shipping_label'     => (string) ($tx->shipping_label ?? ''),
                'payment_proof_path'  => (string) ($tx->payment_proof_path ?? ''),
                'payment_admin_note'  => (string) ($tx->payment_admin_note ?? ''),
                'expires_at'         => $tx->expires_at?->toIso8601String() ?? now()->addMinutes(30)->toIso8601String(),
                'created_at'         => $tx->created_at?->toIso8601String(),
                'address_snapshot'   => [
                    'shipping_recipient_name' => $tx->shipping_recipient_name,
                    'shipping_phone'          => $tx->shipping_phone,
                    'shipping_address_line'   => $tx->shipping_address_line,
                    'shipping_city'           => $tx->shipping_city,
                    'shipping_province'       => $tx->shipping_province,
                    'shipping_postal_code'    => $tx->shipping_postal_code,
                ],
                'items' => $tx->details->map(fn($d) => [
                    'name'    => $d->product_name,
                    'variant' => (string) ($d->variant_name ?? ''),
                    'note'    => (string) ($d->item_note ?? ''),
                    'image'   => (string) ($d->image ?? ''),
                    'price'   => (int) $d->price,
                    'qty'     => (int) $d->quantity,
                ])->all(),
            ];

            session()->put('checkout_waiting.' . $orderId, $data);
        }

        if (($tx?->payment_type ?? '') === 'manual_transfer') {
            $data['transaction_status'] = (string) $tx->status;
            $data['transaction_db_id'] = (int) $tx->id;
            $data['payment_proof_path'] = (string) ($tx->payment_proof_path ?? '');
            $data['payment_admin_note'] = (string) ($tx->payment_admin_note ?? '');
            session()->put('checkout_waiting.' . $orderId, $data);
        } elseif ($this->isExpired($data) && !$this->shouldBypassExpiryForStatus((string) ($tx?->status ?? 'pending'))) {
            $this->cancelMidtransTransaction($orderId);
            $data['transaction_status'] = 'expire';
            session()->put('checkout_waiting.' . $orderId, $data);
            $this->syncTransactionStatus($orderId, 'expire');
        }

        return view('frontend.checkout-waiting', [
            'payment' => $data,
            'manualPaymentSettings' => StoreSetting::manualPayment(),
        ]);
    }

    public function status(Request $request, string $orderId)
    {
        try {
            $localTx = Transaction::query()
                ->where('order_id', $orderId)
                ->where('user_id', $request->user()?->id)
                ->first();

            if ($localTx && $this->shouldBypassExpiryForStatus((string) $localTx->status)) {
                $returnStatus = strtolower((string) $localTx->status) === 'dibatalkan'
                    ? 'cancel'
                    : (string) $localTx->status;

                return response()->json([
                    'transaction_status' => $returnStatus,
                    'payment_type' => (string) ($localTx->payment_type ?? ''),
                    'order_id' => $orderId,
                ]);
            }

            if ($localTx && (string) $localTx->payment_type === 'manual_transfer') {
                return response()->json([
                    'transaction_status' => (string) $localTx->status,
                    'payment_type' => 'manual_transfer',
                    'order_id' => $orderId,
                ]);
            }

            $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);

            $sessionData = session('checkout_waiting.' . $orderId);
            if (
                is_array($sessionData) &&
                $this->isExpired($sessionData) &&
                !$this->shouldBypassExpiryForStatus((string) ($localTx?->status ?? 'pending'))
            ) {
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

            // If DB already says dibatalkan, don't let Midtrans 'pending' overwrite it
            $dbTx = Transaction::query()->where('order_id', $orderId)->first();
            if ($dbTx && strtolower((string) $dbTx->status) === 'dibatalkan') {
                return response()->json([
                    'transaction_status' => 'cancel',
                    'payment_type' => (string) ($json['payment_type'] ?? ''),
                    'order_id' => $orderId,
                ]);
            }

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
        $validated = $request->validate([
            'cancel_reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $tx = Transaction::query()
                ->where('order_id', $orderId)
                ->where('user_id', $request->user()?->id)
                ->first();

            if ($tx && (string) $tx->payment_type === 'manual_transfer') {
                if ($this->shouldBypassExpiryForStatus((string) $tx->status)) {
                    return response()->json([
                        'message' => 'Transaksi tidak dapat dibatalkan karena sudah diproses.',
                    ], 422);
                }

                $oldStatus = (string) $tx->status;
                $tx->status = 'dibatalkan';
                $tx->cancel_reason = $validated['cancel_reason'] ?? null;
                $tx->cancelled_at = now();
                $tx->save();

                TransactionStatusHistory::create([
                    'transaction_id' => $tx->id,
                    'user_id' => $request->user()?->id,
                    'from_status' => $oldStatus,
                    'to_status' => 'dibatalkan',
                    'type' => 'order_cancelled',
                    'note' => $validated['cancel_reason'] ?? 'Transaksi manual dibatalkan.',
                ]);

                return response()->json([
                    'ok' => true,
                    'transaction_status' => 'cancel',
                ]);
            }

            $this->cancelMidtransTransaction($orderId);

            if ($tx && !in_array(strtolower((string) $tx->status), ['dibatalkan', 'cancel', 'expire'], true)) {
                if ($this->shouldBypassExpiryForStatus((string) $tx->status)) {
                    return response()->json([
                        'message' => 'Transaksi tidak dapat dibatalkan karena sudah diproses.',
                    ], 422);
                }

                $tx->status = 'dibatalkan';
                $tx->cancel_reason = $validated['cancel_reason'] ?? null;
                $tx->cancelled_at = now();
                $tx->save();

                TransactionStatusHistory::create([
                    'transaction_id' => $tx->id,
                    'user_id' => $request->user()?->id,
                    'from_status' => null,
                    'to_status' => 'dibatalkan',
                    'type' => 'order_cancelled',
                    'note' => $validated['cancel_reason'] ?? 'Transaksi dibatalkan.',
                ]);

                if ($tx->user_id) {
                    UserNotification::create([
                        'user_id' => $tx->user_id,
                        'type'    => 'order_cancelled',
                        'title'   => 'Pesanan Dibatalkan',
                        'body'    => 'Pesanan ' . $tx->invoice_no . ' telah dibatalkan.',
                        'url'     => route('frontend.profil') . '?tab=pesanan',
                    ]);
                }
            }

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
            'payment_type' => ['nullable', 'string'],
            'reference_value' => ['nullable', 'string'],
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
            $statusJson = $statusRes->json();
            $currentStatus = strtolower((string) ($statusJson['transaction_status'] ?? 'pending'));
            $currentPaymentType = (string) ($statusJson['payment_type'] ?? $tx->payment_type ?? '');

            if (!in_array($currentStatus, ['settlement', 'capture', 'paid'], true)) {
                $requestedPaymentType = strtolower(trim((string) ($validated['payment_type'] ?? '')));
                $referenceValue = trim((string) ($validated['reference_value'] ?? ''));
                $normalizedCurrentType = strtolower(trim((string) $currentPaymentType));
                $vaBank = strtolower((string) ($tx->payment_va_bank ?? ''));
                $midtransTransactionId = (string) ($statusJson['transaction_id'] ?? $tx->midtrans_transaction_id ?? '');
                $sessionPayment = session('checkout_waiting.' . $orderId, []);
                $sessionQr = is_array($sessionPayment) ? (string) ($sessionPayment['qr_url'] ?? '') : '';
                $sessionVa = is_array($sessionPayment) ? (string) ($sessionPayment['va_number'] ?? '') : '';

                $isQris = in_array($requestedPaymentType, ['qris', 'gopay'], true)
                    || in_array($normalizedCurrentType, ['qris', 'gopay'], true)
                    || str_contains(strtolower($referenceValue), 'qr');

                if ($isQris) {
                    $qrUrl = $referenceValue !== '' ? $referenceValue : (string) ($tx->payment_qr_url ?: $sessionQr);
                    if ($qrUrl === '') {
                        throw new RuntimeException('QR code URL tidak ditemukan untuk transaksi ini.');
                    }
                    $this->triggerQrisSimulatorPayment($qrUrl);
                } else {
                    $bank = $vaBank !== '' ? $vaBank : 'bca';
                    $vaNumber = $referenceValue !== '' ? $referenceValue : (string) ($tx->payment_va_number ?: $sessionVa);
                    if ($vaNumber === '') {
                        throw new RuntimeException('VA number tidak ditemukan untuk transaksi ini.');
                    }
                    $this->triggerVaSimulatorPayment($bank, $vaNumber);
                }

                $statusTarget = $midtransTransactionId !== '' ? $midtransTransactionId : $orderId;
                $maxTries = 8;
                for ($i = 0; $i < $maxTries; $i++) {
                    $statusRes = Http::timeout(30)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Authorization' => 'Basic ' . $auth,
                        ])->get('https://api.sandbox.midtrans.com/v2/' . $statusTarget . '/status');

                    if ($statusRes->successful()) {
                        $statusJson = $statusRes->json();
                        $currentStatus = strtolower((string) ($statusJson['transaction_status'] ?? 'pending'));
                        if (in_array($currentStatus, ['settlement', 'capture', 'paid'], true)) {
                            break;
                        }
                    }
                    usleep(800000);
                }

                if (!in_array($currentStatus, ['settlement', 'capture', 'paid'], true)) {
                    throw new RuntimeException('Simulator berhasil dipanggil, tetapi status Midtrans belum berubah. Coba klik lagi dalam 2-3 detik.');
                }
            }

            $this->syncTransactionStatus($orderId, $currentStatus);

            $sessionPayment = session('checkout_waiting.' . $orderId, []);
            if (is_array($sessionPayment) && !empty($sessionPayment)) {
                $sessionPayment['transaction_status'] = $currentStatus;
                session()->put('checkout_waiting.' . $orderId, $sessionPayment);
            }

            return response()->json([
                'ok' => true,
                'message' => 'Simulasi pembayaran berhasil.',
                'transaction_status' => $currentStatus,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    private function triggerQrisSimulatorPayment(string $qrUrl): void
    {
        $res = Http::asForm()
            ->timeout(30)
            ->withOptions(['allow_redirects' => true])
            ->post('https://simulator.sandbox.midtrans.com/v2/qris/payment', [
                'qrCodeUrl' => $qrUrl,
            ]);

        if (!$res->successful()) {
            throw new RuntimeException('Gagal memanggil QRIS simulator. code=' . $res->status());
        }
    }

    private function triggerVaSimulatorPayment(string $bank, string $vaNumber): void
    {
        $bankSlug = strtolower(trim($bank)) ?: 'bca';
        $bankMap = [
            'bca' => 'BCA',
            'bni' => 'BNI',
            'bri' => 'BRI',
            'cimb' => 'CIMB',
            'permata' => 'PERMATA',
            'mandiri' => 'MANDIRI',
            'danamon' => 'DANAMON',
            'bsi' => 'BSI',
            'seabank' => 'SEABANK',
            'saqu' => 'SAQU',
        ];
        $bankUpper = $bankMap[$bankSlug] ?? strtoupper($bankSlug);

        $inquiryUrl = 'https://simulator.sandbox.midtrans.com/openapi/va/inquiry?bank=' . $bankSlug;
        $inquiryResponse = Http::asForm()
            ->timeout(30)
            ->withOptions(['allow_redirects' => true])
            ->post($inquiryUrl, [
                'bank' => $bankUpper,
                'vaNumber' => $vaNumber,
            ]);

        if (!$inquiryResponse->successful()) {
            throw new RuntimeException('Gagal inquiry VA simulator. code=' . $inquiryResponse->status());
        }

        $html = (string) $inquiryResponse->body();
        if ($html === '') {
            return;
        }

        $paymentPath = $this->extractSimulatorPaymentActionPath($html);
        if ($paymentPath === null) {
            return;
        }

        $paymentFormData = $this->extractSimulatorInputs($html);
        if (!isset($paymentFormData['vaNumber']) && !isset($paymentFormData['va_number'])) {
            $paymentFormData['vaNumber'] = $vaNumber;
        }
        if (!isset($paymentFormData['bank'])) {
            $paymentFormData['bank'] = $bankUpper;
        }

        $paymentUrl = str_starts_with($paymentPath, 'http')
            ? $paymentPath
            : 'https://simulator.sandbox.midtrans.com' . (str_starts_with($paymentPath, '/') ? $paymentPath : '/' . $paymentPath);

        $paymentResponse = Http::asForm()
            ->timeout(30)
            ->withOptions(['allow_redirects' => true])
            ->post($paymentUrl, $paymentFormData);

        if (!$paymentResponse->successful()) {
            throw new RuntimeException('Gagal submit pembayaran VA simulator. code=' . $paymentResponse->status());
        }
    }

    private function extractSimulatorPaymentActionPath(string $html): ?string
    {
        if (!preg_match('/<form[^>]+action=\"([^\"]*payment[^\"]*)\"/i', $html, $m)) {
            return null;
        }

        return trim((string) ($m[1] ?? '')) ?: null;
    }

    private function extractSimulatorInputs(string $html): array
    {
        $result = [];
        if (preg_match_all('/<input[^>]+name=\"([^\"]+)\"[^>]*>/i', $html, $matches)) {
            foreach ($matches[1] as $index => $name) {
                $fullTag = $matches[0][$index] ?? '';
                $value = '';
                if (preg_match('/value=\"([^\"]*)\"/i', $fullTag, $vm)) {
                    $value = html_entity_decode((string) ($vm[1] ?? ''), ENT_QUOTES);
                }
                if ($name !== '') {
                    $result[$name] = $value;
                }
            }
        }

        return $result;
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
            $discountAmount = min($subtotal, max(0, (int) ($payment['discount_amount'] ?? 0)));
            $grandTotal = max(0, $subtotal + $shippingCost - $discountAmount);

            $transaction = Transaction::query()->firstOrNew(['order_id' => $orderId]);
            $isNew = !$transaction->exists;
            if ($isNew) {
                $transaction->invoice_no = $this->generateDailyInvoiceNo();
            }

            $snapshot = $payment['address_snapshot'] ?? [];
            $transaction->fill([
                'user_id' => $request->user()?->id,
                'midtrans_transaction_id' => (string) ($payment['transaction_id'] ?? ''),
                'payment_type' => (string) ($payment['payment_type'] ?? ''),
                'payment_method' => (string) ($payment['method_label'] ?? ''),
                'payment_va_number' => (string) ($payment['va_number'] ?? ''),
                'payment_va_bank' => (string) ($payment['va_bank'] ?? ''),
                'payment_qr_url' => (string) ($payment['qr_url'] ?? ''),
                'status' => (string) ($payment['transaction_status'] ?? 'pending'),
                'subtotal_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'coupon_code' => (string) ($payment['coupon_code'] ?? ''),
                'discount_amount' => $discountAmount,
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
                    'item_note' => !empty($item['redeemPoints'])
                        ? LoyaltyPointService::buildRedeemItemNote((int) $item['redeemPoints'], (string) ($item['note'] ?? ''))
                        : (string) ($item['note'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if (!empty($detailRows)) {
                TransactionDetail::query()->where('transaction_id', $transaction->id)->delete();
                TransactionDetail::query()->insert($detailRows);
            }

            if ($isNew) {
                app(LoyaltyPointService::class)->reserveRedeemPoints($transaction);

                if ($discountAmount > 0 && (string) ($payment['coupon_code'] ?? '') !== '') {
                    Coupon::query()->where('code', (string) $payment['coupon_code'])->increment('used_count');
                }
                $userId = $request->user()?->id;
                $userEmail = $request->user()?->email;
                if ($userEmail) {
                    $transaction->load('details', 'user');
                    Mail::to($userEmail)->send(new InvoiceOrder($transaction));
                }
                if ($userId) {
                    UserNotification::create([
                        'user_id' => $userId,
                        'type'    => 'transaction_created',
                        'title'   => 'Pesanan Berhasil Dibuat',
                        'body'    => 'Pesanan ' . $transaction->invoice_no . ' sedang menunggu pembayaran. Segera selesaikan pembayaran sebelum kedaluwarsa.',
                        'url'     => route('frontend.checkout.waiting', ['orderId' => $transaction->order_id]),
                    ]);
                }
                TransactionStatusHistory::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $request->user()?->id,
                    'from_status' => null,
                    'to_status' => (string) $transaction->status,
                    'type' => 'checkout',
                    'note' => 'Checkout dibuat melalui Midtrans.',
                ]);
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

        $prevStatus = (string) $tx->status;
        $isPaid = in_array(strtolower($status), ['settlement', 'capture', 'paid'], true);
        $isCancelled = in_array(strtolower($status), ['cancel', 'expire', 'deny'], true);

        if ($isCancelled) {
            $tx->status = 'dibatalkan';
            if (!$tx->cancelled_at) {
                $tx->cancelled_at = now();
            }
            if (!$tx->cancel_reason) {
                $tx->cancel_reason = strtolower($status) === 'expire' ? 'Transaksi kadaluarsa (tidak dibayar tepat waktu)' : 'Dibatalkan oleh sistem';
            }
            app(LoyaltyPointService::class)->releaseRedeemReservation($tx);
        } else {
            $tx->status = $status;
        }

        if ($isPaid && !$tx->paid_at) {
            $tx->paid_at = now();
        }
        $tx->save();

        if ($prevStatus !== (string) $tx->status) {
            TransactionStatusHistory::create([
                'transaction_id' => $tx->id,
                'user_id' => null,
                'from_status' => $prevStatus,
                'to_status' => (string) $tx->status,
                'type' => 'payment_status_sync',
                'note' => 'Sinkronisasi status pembayaran.',
            ]);
        }

        if ($isPaid && !in_array(strtolower($prevStatus), ['settlement', 'capture', 'paid'], true) && $tx->user_id) {
            app(LoyaltyPointService::class)->finalizeRedeemReservation($tx);

            UserNotification::create([
                'user_id' => $tx->user_id,
                'type'    => 'payment_received',
                'title'   => 'Pembayaran Dikonfirmasi',
                'body'    => 'Pembayaran untuk pesanan ' . $tx->invoice_no . ' telah berhasil dikonfirmasi. Pesanan sedang disiapkan.',
                'url'     => route('frontend.profil') . '?tab=pesanan',
            ]);
        }
    }

    private function shouldBypassExpiryForStatus(string $status): bool
    {
        return in_array(
            strtolower(trim($status)),
            ['settlement', 'capture', 'paid', 'process', 'kirim', 'selesai', 'completed', 'dibatalkan', 'cancel'],
            true
        );
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
