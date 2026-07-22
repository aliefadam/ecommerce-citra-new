<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\QuotationDetail;
use App\Models\QuotationStatusHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderStatusHistory;
use App\Models\User;
use App\Services\DocumentFinancials;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuotationController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(private readonly DocumentNumberGenerator $documentNumberGenerator) {}

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $keyword = trim((string) $request->query('q', ''));

        $quotations = Quotation::query()
            ->with('user')
            ->where('company_id', $this->activeCompanyId())
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('quotation_no', 'like', '%'.$keyword.'%')
                        ->orWhere('manual_customer_name', 'like', '%'.$keyword.'%')
                        ->orWhere('manual_customer_email', 'like', '%'.$keyword.'%')
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$keyword.'%')
                            ->orWhere('email', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.quotations.index', [
            'quotations' => $quotations,
            'filterStatus' => $status,
            'filterKeyword' => $keyword,
        ]);
    }

    public function create()
    {
        return view('backend.quotations.create', [
            'defaultPpnRate' => DocumentFinancials::defaultPpnRate($this->activeCompanyId()),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateForm($request);

        $quotation = DB::transaction(function () use ($request, $validated) {
            $companyId = $this->activeCompanyId();
            $items = $this->prepareItems($validated['items'], $companyId);

            $subtotal = collect($items)->sum('subtotal');
            $discountAmount = min($subtotal, max(0, (int) ($validated['discount_amount'] ?? 0)));
            $financials = DocumentFinancials::compute(
                $subtotal,
                $discountAmount,
                (float) ($validated['ppn_rate'] ?? 0),
                max(0, (int) ($validated['shipping_cost'] ?? 0)),
                max(0, (int) ($validated['admin_fee'] ?? 0)),
                max(0, (int) ($validated['other_cost'] ?? 0)),
            );
            $customerMode = (string) $validated['customer_mode'];

            $quotation = Quotation::create([
                'company_id' => $companyId,
                'quotation_no' => $this->documentNumberGenerator->generate(Quotation::class, 'QUO', $companyId),
                'user_id' => $customerMode === 'existing' ? (int) $validated['customer_id'] : null,
                'manual_customer_name' => $customerMode === 'manual' ? (string) $validated['manual_customer_name'] : null,
                'manual_customer_phone' => $customerMode === 'manual' ? (string) $validated['manual_customer_phone'] : null,
                'manual_customer_email' => $customerMode === 'manual' ? (string) ($validated['manual_customer_email'] ?? '') : null,
                'status' => Quotation::STATUS_DRAFT,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                ...$financials,
                'other_cost_note' => $validated['other_cost_note'] ?? null,
                'valid_until' => $validated['valid_until'],
                'note' => $validated['note'] ?? null,
                'created_by_admin_id' => $request->user()?->id,
            ]);

            foreach ($items as $item) {
                $quotation->details()->create($item);
            }

            QuotationStatusHistory::create([
                'quotation_id' => $quotation->id,
                'user_id' => $request->user()?->id,
                'from_status' => null,
                'to_status' => Quotation::STATUS_DRAFT,
                'note' => 'Quotation dibuat.',
                'created_at' => now(),
            ]);

            return $quotation;
        });

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation berhasil dibuat.');
    }

    public function show(Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        $quotation->load(['details', 'statusHistories.user', 'user', 'createdByAdmin', 'salesOrders']);

        return view('backend.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);
        if ($quotation->isReadOnly()) {
            return redirect()->route('quotations.show', $quotation)->withErrors(['quotation' => 'Quotation ini sudah tidak bisa diedit.']);
        }

        $quotation->load('details');

        return view('backend.quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);
        if ($quotation->isReadOnly()) {
            throw ValidationException::withMessages(['quotation' => 'Quotation ini sudah tidak bisa diedit.']);
        }

        $itemsEditable = $quotation->itemsAreEditable();
        $validated = $this->validateForm($request, requireItems: $itemsEditable);

        DB::transaction(function () use ($request, $validated, $quotation, $itemsEditable) {
            $customerMode = (string) $validated['customer_mode'];
            $updates = [
                'user_id' => $customerMode === 'existing' ? (int) $validated['customer_id'] : null,
                'manual_customer_name' => $customerMode === 'manual' ? (string) $validated['manual_customer_name'] : null,
                'manual_customer_phone' => $customerMode === 'manual' ? (string) $validated['manual_customer_phone'] : null,
                'manual_customer_email' => $customerMode === 'manual' ? (string) ($validated['manual_customer_email'] ?? '') : null,
                'valid_until' => $validated['valid_until'],
                'note' => $validated['note'] ?? null,
            ];

            if ($itemsEditable) {
                $items = $this->prepareItems($validated['items'], $quotation->company_id);
                $subtotal = collect($items)->sum('subtotal');
                $discountAmount = min($subtotal, max(0, (int) ($validated['discount_amount'] ?? 0)));
                $financials = DocumentFinancials::compute(
                    $subtotal,
                    $discountAmount,
                    (float) ($validated['ppn_rate'] ?? 0),
                    max(0, (int) ($validated['shipping_cost'] ?? 0)),
                    max(0, (int) ($validated['admin_fee'] ?? 0)),
                    max(0, (int) ($validated['other_cost'] ?? 0)),
                );

                $updates['subtotal_amount'] = $subtotal;
                $updates['discount_amount'] = $discountAmount;
                $updates = [...$updates, ...$financials, 'other_cost_note' => $validated['other_cost_note'] ?? null];

                $quotation->details()->delete();
                foreach ($items as $item) {
                    $quotation->details()->create($item);
                }
            }

            $quotation->update($updates);
        });

        return redirect()->route('quotations.show', $quotation)->with('success', 'Quotation berhasil diperbarui.');
    }

    public function send(Request $request, Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);
        if ($quotation->status !== Quotation::STATUS_DRAFT) {
            throw ValidationException::withMessages(['quotation' => 'Hanya quotation berstatus draft yang bisa dikirim.']);
        }

        $this->transitionStatus($quotation, Quotation::STATUS_SENT, $request->user(), 'Quotation dikirim ke customer.');

        return back()->with('success', 'Quotation ditandai sebagai terkirim.');
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        $validated = $request->validate([
            'to_status' => ['required', Rule::in([Quotation::STATUS_ACCEPTED, Quotation::STATUS_REJECTED])],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! in_array($quotation->status, [Quotation::STATUS_DRAFT, Quotation::STATUS_SENT], true)) {
            throw ValidationException::withMessages(['quotation' => 'Status quotation saat ini tidak bisa diubah ke accepted/rejected.']);
        }

        $this->transitionStatus($quotation, $validated['to_status'], $request->user(), $validated['note'] ?? null);

        return back()->with('success', 'Status quotation berhasil diperbarui.');
    }

    public function close(Request $request, Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        $validated = $request->validate([
            'close_reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($quotation->isReadOnly()) {
            throw ValidationException::withMessages(['quotation' => 'Quotation ini sudah dalam status akhir.']);
        }
        if ($quotation->salesOrdersCount() < 1) {
            throw ValidationException::withMessages(['quotation' => 'Quotation hanya bisa ditutup manual setelah minimal satu Sales Order dibuat.']);
        }

        DB::transaction(function () use ($request, $validated, $quotation) {
            $quotation->update([
                'closed_at' => now(),
                'closed_by_admin_id' => $request->user()?->id,
                'close_reason' => $validated['close_reason'] ?? null,
            ]);

            $this->transitionStatus($quotation, Quotation::STATUS_CLOSED, $request->user(), $validated['close_reason'] ?? 'Ditutup manual oleh admin.');
        });

        return back()->with('success', 'Quotation berhasil ditutup.');
    }

    public function convertForm(Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        if (! $this->isConvertible($quotation)) {
            return redirect()->route('quotations.show', $quotation)->withErrors(['quotation' => 'Quotation ini tidak bisa dikonversi ke Sales Order saat ini.']);
        }

        $quotation->load('details');

        return view('backend.quotations.convert', [
            'quotation' => $quotation,
            'defaultPpnRate' => DocumentFinancials::defaultPpnRate($quotation->company_id),
        ]);
    }

    public function convert(Request $request, Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.quotation_detail_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'other_cost' => ['nullable', 'integer', 'min:0'],
            'other_cost_note' => ['nullable', 'string', 'max:255'],
        ]);

        $salesOrder = DB::transaction(function () use ($request, $validated, $quotation) {
            // Row lock the Quotation to serialize concurrent convert attempts against the same quotation.
            $locked = Quotation::query()->lockForUpdate()->findOrFail($quotation->id);

            if (! $this->isConvertible($locked)) {
                throw ValidationException::withMessages(['quotation' => 'Quotation ini tidak bisa dikonversi ke Sales Order saat ini.']);
            }

            $selectedItems = collect($validated['items'])->filter(fn ($item) => (int) $item['qty'] > 0)->values();
            if ($selectedItems->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Pilih minimal satu item dengan qty lebih dari 0 untuk dikonversi.']);
            }

            $details = QuotationDetail::query()
                ->where('quotation_id', $locked->id)
                ->whereIn('id', $selectedItems->pluck('quotation_detail_id'))
                ->get()
                ->keyBy('id');

            $soDetailsData = [];
            $subtotal = 0;

            foreach ($selectedItems as $item) {
                $detail = $details->get((int) $item['quotation_detail_id']);
                if (! $detail) {
                    throw ValidationException::withMessages(['items' => 'Item quotation tidak ditemukan.']);
                }

                $qty = (int) $item['qty'];
                $remaining = $detail->remainingQuantity();
                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => 'Qty untuk "'.$detail->product_name.'" melebihi sisa qty ('.$remaining.').']);
                }

                $subtotal += $qty * $detail->price;

                $soDetailsData[] = [
                    'quotation_detail_id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'product_variant_id' => $detail->product_variant_id,
                    'product_name' => $detail->product_name,
                    'variant_name' => $detail->variant_name,
                    'sku' => $detail->sku,
                    'price' => $detail->price,
                    'quantity' => $qty,
                ];
            }

            $companyId = $locked->company_id;
            $financials = DocumentFinancials::compute(
                $subtotal,
                0,
                (float) ($validated['ppn_rate'] ?? 0),
                max(0, (int) ($validated['shipping_cost'] ?? 0)),
                max(0, (int) ($validated['admin_fee'] ?? 0)),
                max(0, (int) ($validated['other_cost'] ?? 0)),
            );

            $salesOrder = SalesOrder::create([
                'company_id' => $companyId,
                'sales_order_no' => $this->documentNumberGenerator->generate(SalesOrder::class, 'SO', $companyId),
                'quotation_id' => $locked->id,
                'user_id' => $locked->user_id,
                'manual_customer_name' => $locked->manual_customer_name,
                'manual_customer_phone' => $locked->manual_customer_phone,
                'manual_customer_email' => $locked->manual_customer_email,
                'status' => SalesOrder::STATUS_CONFIRMED,
                'subtotal_amount' => $subtotal,
                'discount_amount' => 0,
                ...$financials,
                'other_cost_note' => $validated['other_cost_note'] ?? null,
                'created_by_admin_id' => $request->user()?->id,
            ]);

            foreach ($soDetailsData as $data) {
                $salesOrder->details()->create($data);
            }

            SalesOrderStatusHistory::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => $request->user()?->id,
                'from_status' => null,
                'to_status' => SalesOrder::STATUS_CONFIRMED,
                'note' => 'Dibuat dari Quotation '.$locked->quotation_no.'.',
                'created_at' => now(),
            ]);

            $this->recomputeStatusAfterConversion($locked, $request->user());

            return $salesOrder;
        });

        return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Sales Order berhasil dibuat.');
    }

    private function isConvertible(Quotation $quotation): bool
    {
        return in_array($quotation->status, [Quotation::STATUS_ACCEPTED, Quotation::STATUS_PARTIALLY_CONVERTED], true)
            && ! $quotation->isExpiredByDate();
    }

    private function recomputeStatusAfterConversion(Quotation $quotation, ?User $user): void
    {
        $quotation->load('details');
        $totalRemaining = $quotation->details->sum(fn (QuotationDetail $detail) => $detail->remainingQuantity());

        if ($totalRemaining <= 0) {
            $this->transitionStatus($quotation, Quotation::STATUS_CLOSED, $user, 'Seluruh qty sudah dikonversi ke Sales Order.');
        } elseif ($quotation->status !== Quotation::STATUS_PARTIALLY_CONVERTED) {
            $this->transitionStatus($quotation, Quotation::STATUS_PARTIALLY_CONVERTED, $user, 'Sales Order pertama dibuat dari quotation ini.');
        }
    }

    public function print(Quotation $quotation)
    {
        $this->guardCompanyOwnership($quotation->company_id);

        $quotation->load(['details', 'user', 'company']);

        return view('backend.quotations.print', compact('quotation'));
    }

    public function searchCustomers(Request $request)
    {
        return app(AdminManualTransactionController::class)->searchCustomers($request);
    }

    public function searchProducts(Request $request)
    {
        return app(AdminManualTransactionController::class)->searchProducts($request);
    }

    private function transitionStatus(Quotation $quotation, string $toStatus, ?User $user, ?string $note): void
    {
        $fromStatus = $quotation->status;
        $quotation->update(['status' => $toStatus]);

        QuotationStatusHistory::create([
            'quotation_id' => $quotation->id,
            'user_id' => $user?->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    private function validateForm(Request $request, bool $requireItems = true): array
    {
        $itemRules = $requireItems
            ? ['required', 'array', 'min:1']
            : ['nullable', 'array'];

        return $request->validate([
            'customer_mode' => ['required', 'in:existing,manual'],
            'customer_id' => ['nullable', 'required_if:customer_mode,existing', 'integer', 'exists:users,id'],
            'manual_customer_name' => ['nullable', 'required_if:customer_mode,manual', 'string', 'max:150'],
            'manual_customer_phone' => ['nullable', 'required_if:customer_mode,manual', 'string', 'max:50'],
            'manual_customer_email' => ['nullable', 'email', 'max:150'],
            'items' => $itemRules,
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'integer', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'other_cost' => ['nullable', 'integer', 'min:0'],
            'other_cost_note' => ['nullable', 'string', 'max:255'],
            'valid_until' => ['required', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function prepareItems(array $items, int $companyId): array
    {
        $prepared = [];

        foreach ($items as $index => $item) {
            $variant = ProductVariant::query()
                ->with('product')
                ->find((int) $item['product_variant_id']);

            if (! $variant || ! $variant->product) {
                throw ValidationException::withMessages(['items.'.$index.'.product_variant_id' => 'Produk tidak ditemukan.']);
            }

            if ((int) $variant->product->company_id !== $companyId) {
                throw ValidationException::withMessages(['items.'.$index.'.product_variant_id' => 'Produk "'.$variant->product->name.'" bukan milik perusahaan yang sedang aktif.']);
            }

            $qty = max(1, (int) $item['qty']);
            $price = max(0, (int) $item['price']);

            $prepared[] = [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'product_name' => $variant->product->name,
                'variant_name' => $variant->skuLabel(),
                'sku' => (string) ($variant->sku ?? ''),
                'image' => (string) ($variant->image ?? ''),
                'original_price' => (int) round((float) $variant->price),
                'price' => $price,
                'quantity' => $qty,
                'subtotal' => $qty * $price,
                'item_note' => (string) ($item['note'] ?? ''),
            ];
        }

        return $prepared;
    }
}
