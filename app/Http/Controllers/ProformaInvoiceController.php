<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\ProformaInvoice;
use App\Models\SalesOrder;
use App\Services\DocumentFinancials;
use App\Services\DocumentNumberGenerator;
use App\Services\DocumentPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProformaInvoiceController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(
        private readonly DocumentNumberGenerator $documentNumberGenerator,
        private readonly DocumentPaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $proformaInvoices = ProformaInvoice::query()
            ->with(['user', 'salesOrder'])
            ->where('company_id', $this->activeCompanyId())
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.proforma-invoices.index', [
            'proformaInvoices' => $proformaInvoices,
            'filterStatus' => $status,
        ]);
    }

    public function createForm(SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        if (! $this->canIssue($salesOrder)) {
            return redirect()->route('sales-orders.show', $salesOrder)->withErrors(['sales_order' => 'Proforma Invoice tidak bisa diterbitkan untuk Sales Order ini.']);
        }

        $salesOrder->load('details');

        return view('backend.proforma-invoices.create', [
            'salesOrder' => $salesOrder,
            'defaultPpnRate' => DocumentFinancials::defaultPpnRate($salesOrder->company_id),
        ]);
    }

    public function store(Request $request, SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_detail_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
            'ppn_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shipping_cost' => ['nullable', 'integer', 'min:0'],
            'admin_fee' => ['nullable', 'integer', 'min:0'],
            'other_cost' => ['nullable', 'integer', 'min:0'],
            'other_cost_note' => ['nullable', 'string', 'max:255'],
        ]);

        $proformaInvoice = DB::transaction(function () use ($request, $validated, $salesOrder) {
            $locked = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            if (! $this->canIssue($locked)) {
                throw ValidationException::withMessages(['sales_order' => 'Proforma Invoice tidak bisa diterbitkan untuk Sales Order ini.']);
            }

            $selectedItems = collect($validated['items'])->filter(fn ($item) => (int) $item['qty'] > 0)->values();
            if ($selectedItems->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Pilih minimal satu item dengan qty lebih dari 0.']);
            }

            $details = $locked->details()->whereIn('id', $selectedItems->pluck('sales_order_detail_id'))->get()->keyBy('id');

            $piDetailsData = [];
            $subtotal = 0;

            foreach ($selectedItems as $item) {
                $detail = $details->get((int) $item['sales_order_detail_id']);
                if (! $detail) {
                    throw ValidationException::withMessages(['items' => 'Item Sales Order tidak ditemukan.']);
                }

                $qty = (int) $item['qty'];
                if ($qty > $detail->quantity) {
                    throw ValidationException::withMessages(['items' => 'Qty untuk "'.$detail->product_name.'" melebihi qty pada Sales Order ('.$detail->quantity.').']);
                }

                $subtotal += $qty * $detail->price;

                $piDetailsData[] = [
                    'sales_order_detail_id' => $detail->id,
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

            $proformaInvoice = ProformaInvoice::create([
                'company_id' => $companyId,
                'proforma_invoice_no' => $this->documentNumberGenerator->generate(ProformaInvoice::class, 'PI', $companyId),
                'sales_order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'manual_customer_name' => $locked->manual_customer_name,
                'manual_customer_phone' => $locked->manual_customer_phone,
                'manual_customer_email' => $locked->manual_customer_email,
                'status' => ProformaInvoice::STATUS_ISSUED,
                'subtotal_amount' => $subtotal,
                ...$financials,
                'other_cost_note' => $validated['other_cost_note'] ?? null,
                'paid_amount' => 0,
                'outstanding_amount' => $financials['grand_total'],
                'issued_at' => now(),
                'created_by_admin_id' => $request->user()?->id,
            ]);

            foreach ($piDetailsData as $data) {
                $proformaInvoice->details()->create($data);
            }

            return $proformaInvoice;
        });

        return redirect()->route('proforma-invoices.show', $proformaInvoice)->with('success', 'Proforma Invoice berhasil diterbitkan.');
    }

    public function show(ProformaInvoice $proformaInvoice)
    {
        $this->guardCompanyOwnership($proformaInvoice->company_id);

        $proformaInvoice->load(['details', 'documentPayments.recordedByAdmin', 'user', 'createdByAdmin', 'salesOrder']);

        return view('backend.proforma-invoices.show', compact('proformaInvoice'));
    }

    public function recordPayment(Request $request, ProformaInvoice $proformaInvoice)
    {
        $this->guardCompanyOwnership($proformaInvoice->company_id);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->paymentService->record($proformaInvoice, $validated, $request->user()?->id);

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function cancel(Request $request, ProformaInvoice $proformaInvoice)
    {
        $this->guardCompanyOwnership($proformaInvoice->company_id);

        if (! $proformaInvoice->canBeCancelled()) {
            throw ValidationException::withMessages(['proforma_invoice' => 'Proforma Invoice ini tidak bisa dibatalkan (sudah ada Pembayaran tercatat atau sudah dibatalkan).']);
        }

        $proformaInvoice->update([
            'status' => ProformaInvoice::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_admin_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Proforma Invoice berhasil dibatalkan.');
    }

    public function print(ProformaInvoice $proformaInvoice)
    {
        $this->guardCompanyOwnership($proformaInvoice->company_id);

        $proformaInvoice->load(['details', 'user', 'company']);

        return view('backend.proforma-invoices.print', compact('proformaInvoice'));
    }

    private function canIssue(SalesOrder $salesOrder): bool
    {
        return $salesOrder->status !== SalesOrder::STATUS_CANCELLED && ! $salesOrder->hasActiveProformaInvoice();
    }
}
