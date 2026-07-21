<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\B2bInvoice;
use App\Models\DeliveryNote;
use App\Models\DocumentPayment;
use App\Models\ProformaInvoice;
use App\Models\SalesOrder;
use App\Services\DocumentNumberGenerator;
use App\Services\DocumentPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class B2bInvoiceController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(
        private readonly DocumentNumberGenerator $documentNumberGenerator,
        private readonly DocumentPaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $b2bInvoices = B2bInvoice::query()
            ->with(['user', 'salesOrder'])
            ->where('company_id', $this->activeCompanyId())
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.b2b-invoices.index', [
            'b2bInvoices' => $b2bInvoices,
            'filterStatus' => $status,
        ]);
    }

    public function createForm(SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $candidates = $salesOrder->uninvoicedDeliveryNotes();
        if ($candidates->isEmpty()) {
            return redirect()->route('sales-orders.show', $salesOrder)->withErrors(['sales_order' => 'Tidak ada Surat Jalan yang siap ditagih (harus shipped/delivered dan belum ter-invoice).']);
        }

        $candidates->load('details');

        return view('backend.b2b-invoices.create', [
            'salesOrder' => $salesOrder,
            'candidates' => $candidates,
        ]);
    }

    public function store(Request $request, SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $validated = $request->validate([
            'delivery_note_ids' => ['required', 'array', 'min:1'],
            'delivery_note_ids.*' => ['integer'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $b2bInvoice = DB::transaction(function () use ($request, $validated, $salesOrder) {
            $locked = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            $isFirstInvoiceForSalesOrder = $locked->b2bInvoices()->count() === 0;

            $deliveryNotes = DeliveryNote::query()
                ->where('sales_order_id', $locked->id)
                ->whereIn('id', $validated['delivery_note_ids'])
                ->whereIn('status', [DeliveryNote::STATUS_SHIPPED, DeliveryNote::STATUS_DELIVERED])
                ->whereDoesntHave('b2bInvoices', fn ($q) => $q->where('status', '!=', B2bInvoice::STATUS_CANCELLED))
                ->with('details')
                ->lockForUpdate()
                ->get();

            if ($deliveryNotes->count() !== count($validated['delivery_note_ids'])) {
                throw ValidationException::withMessages(['delivery_note_ids' => 'Salah satu Surat Jalan yang dipilih sudah ter-invoice atau belum terkirim. Silakan muat ulang halaman.']);
            }

            $detailsData = [];
            $subtotal = 0;

            foreach ($deliveryNotes as $deliveryNote) {
                foreach ($deliveryNote->details as $dnDetail) {
                    $price = (int) ($dnDetail->salesOrderDetail?->price ?? 0);
                    $subtotal += $price * $dnDetail->quantity;

                    $detailsData[] = [
                        'delivery_note_detail_id' => $dnDetail->id,
                        'product_name' => $dnDetail->product_name,
                        'variant_name' => $dnDetail->variant_name,
                        'sku' => $dnDetail->sku,
                        'price' => $price,
                        'quantity' => $dnDetail->quantity,
                    ];
                }
            }

            $companyId = $locked->company_id;

            $b2bInvoice = B2bInvoice::create([
                'company_id' => $companyId,
                'b2b_invoice_no' => $this->documentNumberGenerator->generate(B2bInvoice::class, 'INVB', $companyId),
                'sales_order_id' => $locked->id,
                'user_id' => $locked->user_id,
                'manual_customer_name' => $locked->manual_customer_name,
                'manual_customer_phone' => $locked->manual_customer_phone,
                'manual_customer_email' => $locked->manual_customer_email,
                'status' => B2bInvoice::STATUS_ISSUED,
                'subtotal_amount' => $subtotal,
                'grand_total' => $subtotal,
                'paid_amount' => 0,
                'outstanding_amount' => $subtotal,
                'due_date' => $validated['due_date'],
                'issued_at' => now(),
                'created_by_admin_id' => $request->user()?->id,
            ]);

            foreach ($detailsData as $data) {
                $b2bInvoice->details()->create($data);
            }

            $b2bInvoice->deliveryNotes()->attach($deliveryNotes->pluck('id'));

            if ($isFirstInvoiceForSalesOrder) {
                $this->applyDpCreditIfAny($locked, $b2bInvoice, $request->user()?->id);
            }

            return $b2bInvoice;
        });

        return redirect()->route('b2b-invoices.show', $b2bInvoice)->with('success', 'Invoice B2B berhasil dibuat.');
    }

    public function show(B2bInvoice $b2bInvoice)
    {
        $this->guardCompanyOwnership($b2bInvoice->company_id);

        $b2bInvoice->load(['details', 'documentPayments.recordedByAdmin', 'user', 'createdByAdmin', 'salesOrder', 'deliveryNotes']);

        return view('backend.b2b-invoices.show', compact('b2bInvoice'));
    }

    public function recordPayment(Request $request, B2bInvoice $b2bInvoice)
    {
        $this->guardCompanyOwnership($b2bInvoice->company_id);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'payment_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->paymentService->record($b2bInvoice, $validated, $request->user()?->id);

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }

    public function cancel(Request $request, B2bInvoice $b2bInvoice)
    {
        $this->guardCompanyOwnership($b2bInvoice->company_id);

        if (! $b2bInvoice->canBeCancelled()) {
            throw ValidationException::withMessages(['b2b_invoice' => 'Invoice B2B ini tidak bisa dibatalkan (sudah ada Pembayaran tercatat atau sudah dibatalkan).']);
        }

        $b2bInvoice->update([
            'status' => B2bInvoice::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_admin_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Invoice B2B berhasil dibatalkan.');
    }

    public function print(B2bInvoice $b2bInvoice)
    {
        $this->guardCompanyOwnership($b2bInvoice->company_id);

        $b2bInvoice->load(['details', 'user', 'company']);

        return view('backend.b2b-invoices.print', compact('b2bInvoice'));
    }

    /**
     * MVP DP allocation rule: credit only the first Invoice B2B ever created from a
     * Sales Order, sized to whatever has been paid on that Sales Order's (single,
     * MVP-restricted) active Proforma Invoice. Later invoices from the same Sales
     * Order get no further credit (see PRD Rekomendasi MVP).
     */
    private function applyDpCreditIfAny(SalesOrder $salesOrder, B2bInvoice $b2bInvoice, ?int $adminId): void
    {
        $proformaInvoice = $salesOrder->proformaInvoices()
            ->where('status', '!=', ProformaInvoice::STATUS_CANCELLED)
            ->where('paid_amount', '>', 0)
            ->first();

        if (! $proformaInvoice) {
            return;
        }

        $creditAmount = min($proformaInvoice->paid_amount, $b2bInvoice->grand_total);
        if ($creditAmount <= 0) {
            return;
        }

        $this->paymentService->record($b2bInvoice, [
            'amount' => $creditAmount,
            'payment_date' => now()->toDateString(),
            'note' => 'Kredit DP otomatis dari '.$proformaInvoice->proforma_invoice_no,
            'source' => DocumentPayment::SOURCE_DP_CREDIT,
        ], $adminId);
    }
}
