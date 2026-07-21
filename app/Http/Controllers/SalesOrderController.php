<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderStatusHistory;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(private readonly DocumentNumberGenerator $documentNumberGenerator) {}

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');
        $keyword = trim((string) $request->query('q', ''));

        $salesOrders = SalesOrder::query()
            ->with(['user', 'quotation'])
            ->where('company_id', $this->activeCompanyId())
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($inner) use ($keyword) {
                    $inner->where('sales_order_no', 'like', '%'.$keyword.'%')
                        ->orWhere('manual_customer_name', 'like', '%'.$keyword.'%')
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.sales-orders.index', [
            'salesOrders' => $salesOrders,
            'filterStatus' => $status,
            'filterKeyword' => $keyword,
            'canSeePricing' => $this->canSeePricing($request),
        ]);
    }

    public function show(Request $request, SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $salesOrder->load(['details', 'statusHistories.user', 'user', 'createdByAdmin', 'quotation', 'proformaInvoices', 'deliveryNotes', 'b2bInvoices']);

        return view('backend.sales-orders.show', [
            'salesOrder' => $salesOrder,
            'canSeePricing' => $this->canSeePricing($request),
        ]);
    }

    /**
     * Staff Gudang has no quotations.* permission at all — used as a proxy signal
     * to hide price/commercial columns on shared Sales Order pages (PRD: "Staff
     * gudang tidak bisa mengakses data harga/komersial").
     */
    private function canSeePricing(Request $request): bool
    {
        return (bool) $request->user()?->hasAdminPermission('quotations.index');
    }

    public function cancel(Request $request, SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        DB::transaction(function () use ($request, $salesOrder) {
            $locked = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            if (! $locked->canBeCancelled()) {
                throw ValidationException::withMessages(['sales_order' => 'Sales Order ini tidak bisa dibatalkan (sudah ada Surat Jalan aktif atau sudah dibatalkan).']);
            }

            $fromStatus = $locked->status;
            $locked->update([
                'status' => SalesOrder::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by_admin_id' => $request->user()?->id,
            ]);

            SalesOrderStatusHistory::create([
                'sales_order_id' => $locked->id,
                'user_id' => $request->user()?->id,
                'from_status' => $fromStatus,
                'to_status' => SalesOrder::STATUS_CANCELLED,
                'note' => 'Dibatalkan oleh admin, sisa qty dikembalikan ke Quotation.',
                'created_at' => now(),
            ]);

            $this->reopenQuotationIfNeeded($locked, $request);
        });

        return back()->with('success', 'Sales Order berhasil dibatalkan.');
    }

    public function print(SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $salesOrder->load(['details', 'user', 'company']);

        return view('backend.sales-orders.print', compact('salesOrder'));
    }

    /**
     * Sales Order dibuat langsung tanpa Quotation, untuk order yang tidak butuh
     * negosiasi harga formal (lihat PRD §Ringkasan, update 2026-07-22).
     */
    public function create()
    {
        return view('backend.sales-orders.create');
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
            'items.*.price' => ['required', 'integer', 'min:0'],
            'discount_amount' => ['nullable', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $salesOrder = DB::transaction(function () use ($request, $validated) {
            $companyId = $this->activeCompanyId();
            $items = $this->prepareItems($validated['items'], $companyId);

            $subtotal = collect($items)->sum('subtotal');
            $discountAmount = min($subtotal, max(0, (int) ($validated['discount_amount'] ?? 0)));
            $grandTotal = max(0, $subtotal - $discountAmount);
            $customerMode = (string) $validated['customer_mode'];

            $salesOrder = SalesOrder::create([
                'company_id' => $companyId,
                'sales_order_no' => $this->documentNumberGenerator->generate(SalesOrder::class, 'SO', $companyId),
                'quotation_id' => null,
                'user_id' => $customerMode === 'existing' ? (int) $validated['customer_id'] : null,
                'manual_customer_name' => $customerMode === 'manual' ? (string) $validated['manual_customer_name'] : null,
                'manual_customer_phone' => $customerMode === 'manual' ? (string) $validated['manual_customer_phone'] : null,
                'manual_customer_email' => $customerMode === 'manual' ? (string) ($validated['manual_customer_email'] ?? '') : null,
                'status' => SalesOrder::STATUS_CONFIRMED,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discountAmount,
                'grand_total' => $grandTotal,
                'created_by_admin_id' => $request->user()?->id,
            ]);

            foreach ($items as $item) {
                $salesOrder->details()->create([
                    'quotation_detail_id' => null,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'product_name' => $item['product_name'],
                    'variant_name' => $item['variant_name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }

            SalesOrderStatusHistory::create([
                'sales_order_id' => $salesOrder->id,
                'user_id' => $request->user()?->id,
                'from_status' => null,
                'to_status' => SalesOrder::STATUS_CONFIRMED,
                'note' => 'Dibuat langsung tanpa Quotation.',
                'created_at' => now(),
            ]);

            return $salesOrder;
        });

        return redirect()->route('sales-orders.show', $salesOrder)->with('success', 'Sales Order berhasil dibuat.');
    }

    public function searchCustomers(Request $request)
    {
        return app(AdminManualTransactionController::class)->searchCustomers($request);
    }

    public function searchProducts(Request $request)
    {
        return app(AdminManualTransactionController::class)->searchProducts($request);
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
                'price' => $price,
                'quantity' => $qty,
                'subtotal' => $qty * $price,
            ];
        }

        return $prepared;
    }

    private function reopenQuotationIfNeeded(SalesOrder $salesOrder, Request $request): void
    {
        $quotation = Quotation::query()->lockForUpdate()->find($salesOrder->quotation_id);
        if (! $quotation) {
            return;
        }

        $quotation->load('details');
        $totalRemaining = $quotation->details->sum(fn ($detail) => $detail->remainingQuantity());

        $wasAutoClosged = $quotation->status === Quotation::STATUS_CLOSED && $quotation->closed_by_admin_id === null;
        if ($totalRemaining > 0 && $wasAutoClosged) {
            $fromStatus = $quotation->status;
            $quotation->update(['status' => Quotation::STATUS_PARTIALLY_CONVERTED]);

            QuotationStatusHistory::create([
                'quotation_id' => $quotation->id,
                'user_id' => $request->user()?->id,
                'from_status' => $fromStatus,
                'to_status' => Quotation::STATUS_PARTIALLY_CONVERTED,
                'note' => 'Dibuka kembali otomatis karena Sales Order '.$salesOrder->sales_order_no.' dibatalkan.',
                'created_at' => now(),
            ]);
        }
    }
}
