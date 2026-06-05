<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionStatusHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminManualTransactionController extends Controller
{
    public function searchCustomers(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim((string) ($request->query('q', '')));
        $query = User::query()
            ->with(['addresses' => fn ($q) => $q->orderByDesc('is_primary')->latest('id')])
            ->where(function ($q) {
                $q->whereNull('role')->orWhere('role', '!=', 'admin');
            })
            ->whereNull('admin_role_id')
            ->orderBy('name');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                  ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        $results = $query->limit(20)->get()->map(function (User $user) {
            $address = $user->addresses->first();
            return [
                'id'      => $user->id,
                'text'    => $user->name . ' — ' . $user->email,
                'name'    => $user->name,
                'email'   => $user->email,
                'phone'   => trim((string) ($user->phone_country_code ?? '') . (string) ($user->phone_number ?? '')),
                'address' => $address ? [
                    'recipient_name' => $address->recipient_name,
                    'phone'          => trim((string) ($address->phone_country_code ?? '') . (string) ($address->phone_number ?? '')),
                    'address_line'   => $address->address_line,
                    'province'       => $address->province,
                    'city'           => $address->city,
                    'district'       => $address->district,
                    'postal_code'    => $address->postal_code,
                ] : null,
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function searchProducts(Request $request): \Illuminate\Http\JsonResponse
    {
        $term = trim((string) ($request->query('q', '')));
        $query = ProductVariant::query()
            ->with(['product', 'attributeValues.definition'])
            ->whereHas('product')
            ->orderBy('product_id')
            ->orderBy('id');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', '%' . $term . '%'))
                  ->orWhere('sku', 'like', '%' . $term . '%');
            });
        }

        $results = $query->limit(30)->get()->map(function (ProductVariant $variant) {
            $flags = array_filter([
                $variant->sku ? 'SKU ' . $variant->sku : '',
                'Stok ' . (int) $variant->stock,
                $variant->product?->status !== 'active' ? 'Nonaktif' : '',
            ]);
            return [
                'id'           => $variant->id,
                'text'         => ($variant->product?->name ?? 'Produk') . ' - ' . $variant->skuLabel() . ' (' . implode(' | ', $flags) . ')',
                'product_name' => $variant->product?->name ?? 'Produk',
                'variant_name' => $variant->skuLabel(),
                'sku'          => (string) ($variant->sku ?? ''),
                'price'        => (int) round((float) $variant->price),
                'stock'        => (int) $variant->stock,
                'status'       => (string) ($variant->product?->status ?? 'inactive'),
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function create()
    {
        return view('backend.transactions.manual-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => ['required', 'in:existing,manual'],
            'customer_id' => ['nullable', 'required_if:customer_mode,existing', 'integer', 'exists:users,id'],
            'manual_customer_name' => ['nullable', 'required_if:customer_mode,manual', 'string', 'max:150'],
            'manual_customer_phone' => ['nullable', 'required_if:customer_mode,manual', 'string', 'max:50'],
            'manual_customer_email' => ['nullable', 'email', 'max:150'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'integer', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'integer', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $transaction = DB::transaction(function () use ($request, $validated) {
            $items = collect($validated['items'])
                ->map(fn (array $item) => [
                    'product_variant_id' => (int) $item['product_variant_id'],
                    'qty' => max(1, (int) $item['qty']),
                    'unit_price' => max(0, (int) $item['unit_price']),
                    'discount_amount' => max(0, (int) ($item['discount_amount'] ?? 0)),
                    'note' => trim((string) ($item['note'] ?? '')),
                ])
                ->values();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Minimal satu produk wajib ditambahkan.']);
            }

            $subtotal = 0;
            $preparedItems = [];

            foreach ($items as $index => $item) {
                $variant = ProductVariant::query()
                    ->with(['product', 'variant', 'attributeValues.definition'])
                    ->lockForUpdate()
                    ->find($item['product_variant_id']);

                if (!$variant || !$variant->product) {
                    throw ValidationException::withMessages(['items.' . $index . '.product_variant_id' => 'Produk tidak ditemukan.']);
                }

                if ((string) $variant->product->status !== 'active') {
                    throw ValidationException::withMessages(['items.' . $index . '.product_variant_id' => 'Produk "' . $variant->product->name . '" tidak aktif.']);
                }

                $stockBefore = (int) $variant->stock;
                if ($stockBefore < $item['qty']) {
                    throw ValidationException::withMessages(['items.' . $index . '.qty' => 'Stok "' . $variant->product->name . '" tidak mencukupi.']);
                }

                $lineGross = $item['qty'] * $item['unit_price'];
                $itemDiscount = min($lineGross, $item['discount_amount']);
                $itemSubtotal = max(0, $lineGross - $itemDiscount);
                $subtotal += $itemSubtotal;

                $preparedItems[] = compact('variant', 'item', 'stockBefore', 'itemDiscount', 'itemSubtotal');
            }

            $discountAmount = min($subtotal, max(0, (int) ($validated['discount_amount'] ?? 0)));
            $shippingCost = max(0, (int) ($validated['shipping_cost'] ?? 0));
            $ppnRate = max(0, min(100, (float) ($validated['ppn_rate'] ?? 0)));
            $taxableAmount = max(0, $subtotal - $discountAmount);
            $taxAmount = (int) round($taxableAmount * $ppnRate / 100);
            $grandTotal = $taxableAmount + $shippingCost + $taxAmount;
            $customerMode = (string) $validated['customer_mode'];

            $transaction = Transaction::create([
                'user_id' => $customerMode === 'existing' ? (int) $validated['customer_id'] : null,
                'source' => Transaction::SOURCE_MANUAL,
                'created_by_admin_id' => $request->user()?->id,
                'manual_customer_name' => $customerMode === 'manual' ? (string) $validated['manual_customer_name'] : null,
                'manual_customer_phone' => $customerMode === 'manual' ? (string) $validated['manual_customer_phone'] : null,
                'manual_customer_email' => $customerMode === 'manual' ? (string) ($validated['manual_customer_email'] ?? '') : null,
                'invoice_no' => $this->generateDailyInvoiceNo(),
                'order_id' => $this->generateManualOrderId(),
                'payment_type' => 'manual_admin',
                'payment_method' => 'Manual Admin',
                'payment_status' => 'unpaid',
                'payment_amount' => 0,
                'status' => 'pending',
                'subtotal_amount' => $subtotal,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'tax_name' => $ppnRate > 0 ? 'PPN' : null,
                'tax_rate' => $ppnRate,
                'taxable_amount' => $taxableAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'shipping_type' => 'belum_ditentukan',
                'shipping_label' => 'Belum ditentukan',
            ]);

            foreach ($preparedItems as $prepared) {
                /** @var ProductVariant $variant */
                $variant = $prepared['variant'];
                $item = $prepared['item'];
                $stockBefore = (int) $prepared['stockBefore'];
                $stockAfter = $stockBefore - (int) $item['qty'];

                $detail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => (string) $variant->product?->name,
                    'variant_name' => $variant->skuLabel(),
                    'sku' => (string) ($variant->sku ?? ''),
                    'image' => (string) ($variant->image ?? ''),
                    'price' => (int) $item['unit_price'],
                    'discount_amount' => (int) $prepared['itemDiscount'],
                    'quantity' => (int) $item['qty'],
                    'subtotal' => (int) $prepared['itemSubtotal'],
                    'item_note' => $item['note'],
                ]);

                $variant->stock = $stockAfter;
                $variant->save();

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'transaction_detail_id' => $detail->id,
                    'admin_user_id' => $request->user()?->id,
                    'type' => 'out',
                    'quantity' => (int) $item['qty'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'source' => 'manual_transaction',
                    'description' => 'Transaksi manual admin ' . $transaction->invoice_no,
                ]);
            }

            TransactionStatusHistory::create([
                'transaction_id' => $transaction->id,
                'user_id' => $request->user()?->id,
                'from_status' => null,
                'to_status' => 'pending',
                'type' => 'manual_admin_created',
                'note' => 'Transaksi manual dibuat oleh admin.',
            ]);

            return $transaction;
        });

        return redirect()
            ->route('transactions.show', $transaction)
            ->with('success', 'Transaksi manual berhasil dibuat.');
    }

    public function updatePayment(Request $request, Transaction $transaction)
    {
        if ($transaction->normalizedSource() !== Transaction::SOURCE_MANUAL) {
            return back()->withErrors(['payment' => 'Pembayaran manual admin hanya tersedia untuk transaksi manual.']);
        }

        $validated = $request->validate([
            'payment_status' => ['required', Rule::in(['unpaid', 'partial', 'paid', 'cancelled'])],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'payment_paid_at' => ['nullable', 'date'],
            'payment_amount' => ['nullable', 'integer', 'min:0'],
            'payment_admin_note' => ['nullable', 'string', 'max:500'],
            'payment_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
        ]);

        DB::transaction(function () use ($request, $transaction, $validated) {
            $oldStatus = (string) $transaction->status;
            $paymentStatus = (string) $validated['payment_status'];
            $paidAt = !empty($validated['payment_paid_at']) ? \Carbon\Carbon::parse($validated['payment_paid_at']) : null;
            $paymentAmount = max(0, (int) ($validated['payment_amount'] ?? 0));

            if ($paymentStatus === 'paid') {
                $paidAt = $paidAt ?: now();
                $paymentAmount = $paymentAmount > 0 ? $paymentAmount : (int) $transaction->grand_total;
                $transaction->status = 'paid';
                $transaction->paid_at = $transaction->paid_at ?: $paidAt;
                $transaction->payment_verified_at = now();
                $transaction->payment_rejected_at = null;
            } elseif ($paymentStatus === 'cancelled') {
                $transaction->status = 'dibatalkan';
                $transaction->cancelled_at = $transaction->cancelled_at ?: now();
                $transaction->payment_rejected_at = now();
            } elseif (in_array(strtolower((string) $transaction->status), ['paid'], true)) {
                $transaction->status = 'pending';
            }

            if ($request->hasFile('payment_proof')) {
                $oldProof = (string) ($transaction->payment_proof_path ?? '');
                $path = $request->file('payment_proof')->store('payment-proofs', 'public');
                $transaction->payment_proof_path = 'storage/' . $path;
                $transaction->payment_proof_uploaded_at = now();

                if ($oldProof !== '' && str_starts_with($oldProof, 'storage/')) {
                    Storage::disk('public')->delete(substr($oldProof, strlen('storage/')));
                }
            }

            $transaction->payment_status = $paymentStatus;
            $transaction->payment_method = $validated['payment_method'] ?: $transaction->payment_method;
            $transaction->payment_paid_at = $paidAt;
            $transaction->payment_amount = $paymentAmount;
            $transaction->payment_admin_note = $validated['payment_admin_note'] ?? null;
            $transaction->save();

            TransactionStatusHistory::create([
                'transaction_id' => $transaction->id,
                'user_id' => $request->user()?->id,
                'from_status' => $oldStatus,
                'to_status' => (string) $transaction->status,
                'type' => 'manual_admin_payment_update',
                'note' => 'Status pembayaran manual: ' . $transaction->paymentStatusLabel() . '.',
            ]);
        });

        return back()->with('success', 'Pembayaran manual berhasil diperbarui.');
    }

    public function updateShipping(Request $request, Transaction $transaction)
    {
        if ($transaction->normalizedSource() !== Transaction::SOURCE_MANUAL) {
            return back()->withErrors(['shipping' => 'Pengiriman manual hanya tersedia untuk transaksi manual.']);
        }

        $validated = $request->validate([
            'shipping_type' => ['required', Rule::in(['belum_ditentukan', 'dikirim', 'ambil_sendiri', 'kurir_toko', 'ekspedisi_manual', 'gratis_ongkir'])],
            'shipping_recipient_name' => ['nullable', 'string', 'max:150'],
            'shipping_phone' => ['nullable', 'string', 'max:50'],
            'shipping_address_line' => ['nullable', 'string', 'max:1000'],
            'shipping_province' => ['nullable', 'string', 'max:100'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_district' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_courier_name' => ['nullable', 'string', 'max:100'],
            'shipping_service' => ['nullable', 'string', 'max:100'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'shipping_note' => ['nullable', 'string', 'max:500'],
        ]);

        $shippingType = (string) $validated['shipping_type'];
        $requiresAddress = in_array($shippingType, ['dikirim', 'kurir_toko', 'ekspedisi_manual', 'gratis_ongkir'], true);
        if ($requiresAddress) {
            $missing = [];
            foreach (['shipping_recipient_name', 'shipping_phone', 'shipping_address_line'] as $field) {
                if (blank($validated[$field] ?? null)) {
                    $missing[$field] = 'Data pengiriman wajib diisi untuk jenis pengiriman ini.';
                }
            }

            if ($missing !== []) {
                throw ValidationException::withMessages($missing);
            }
        }

        $shippingCost = $shippingType === 'gratis_ongkir'
            ? 0
            : max(0, (int) ($validated['shipping_cost'] ?? 0));

        $courierName = trim((string) ($validated['shipping_courier_name'] ?? ''));
        $service = trim((string) ($validated['shipping_service'] ?? ''));
        $shippingLabel = $courierName !== ''
            ? trim($courierName . ($service !== '' ? ' ' . $service : ''))
            : $this->shippingTypeLabel($shippingType);

        DB::transaction(function () use ($request, $transaction, $validated, $shippingType, $shippingCost, $courierName, $service, $shippingLabel) {
            $oldStatus = (string) $transaction->status;
            $baseTotal = max(0, (int) $transaction->subtotal_amount - (int) $transaction->discount_amount) + (int) ($transaction->tax_amount ?? 0);

            $transaction->shipping_type = $shippingType;
            $transaction->shipping_recipient_name = $validated['shipping_recipient_name'] ?? null;
            $transaction->shipping_phone = $validated['shipping_phone'] ?? null;
            $transaction->shipping_address_line = $validated['shipping_address_line'] ?? null;
            $transaction->shipping_province = $validated['shipping_province'] ?? null;
            $transaction->shipping_city = $validated['shipping_city'] ?? null;
            $transaction->shipping_district = $validated['shipping_district'] ?? null;
            $transaction->shipping_postal_code = $validated['shipping_postal_code'] ?? null;
            $transaction->shipping_courier_name = $courierName !== '' ? $courierName : null;
            $transaction->shipping_service = $service !== '' ? $service : null;
            $transaction->shipping_cost = $shippingCost;
            $transaction->shipping_label = $shippingLabel;
            $transaction->tracking_number = $validated['tracking_number'] ?? null;
            $transaction->shipping_note = $validated['shipping_note'] ?? null;
            $transaction->grand_total = $baseTotal + $shippingCost;
            $transaction->save();

            TransactionStatusHistory::create([
                'transaction_id' => $transaction->id,
                'user_id' => $request->user()?->id,
                'from_status' => $oldStatus,
                'to_status' => (string) $transaction->status,
                'type' => 'manual_admin_shipping_update',
                'note' => 'Data pengiriman manual diperbarui: ' . $transaction->shippingTypeLabel() . '.',
            ]);
        });

        return back()->with('success', 'Pengiriman manual berhasil diperbarui.');
    }

    private function generateDailyInvoiceNo(): string
    {
        $prefix = 'INV-' . now()->format('YmdHis') . '-';
        $sequence = ((int) Transaction::query()
            ->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])
            ->lockForUpdate()
            ->count()) + 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    private function generateManualOrderId(): string
    {
        do {
            $orderId = 'MAN-' . now()->format('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
        } while (Transaction::query()->where('order_id', $orderId)->exists());

        return $orderId;
    }

    private function shippingTypeLabel(string $shippingType): string
    {
        return match ($shippingType) {
            'dikirim' => 'Dikirim',
            'ambil_sendiri' => 'Ambil sendiri',
            'kurir_toko' => 'Kurir toko',
            'ekspedisi_manual' => 'Ekspedisi manual',
            'gratis_ongkir' => 'Gratis ongkir',
            default => 'Belum ditentukan',
        };
    }
}
