<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use App\Models\SalesOrder;
use App\Models\SalesOrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrderController extends Controller
{
    use ScopesToActiveCompany;

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
