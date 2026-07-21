<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\DeliveryNote;
use App\Models\PackingList;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\StockMovement;
use App\Services\DocumentNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryNoteController extends Controller
{
    use ScopesToActiveCompany;

    public function __construct(private readonly DocumentNumberGenerator $documentNumberGenerator) {}

    public function index(Request $request)
    {
        $status = (string) $request->query('status', '');

        $deliveryNotes = DeliveryNote::query()
            ->with('salesOrder')
            ->where('company_id', $this->activeCompanyId())
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('backend.delivery-notes.index', [
            'deliveryNotes' => $deliveryNotes,
            'filterStatus' => $status,
        ]);
    }

    public function createForm(SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        if ($salesOrder->status === SalesOrder::STATUS_CANCELLED) {
            return redirect()->route('sales-orders.show', $salesOrder)->withErrors(['sales_order' => 'Sales Order ini sudah dibatalkan.']);
        }

        $salesOrder->load('details');

        return view('backend.delivery-notes.create', compact('salesOrder'));
    }

    public function store(Request $request, SalesOrder $salesOrder)
    {
        $this->guardCompanyOwnership($salesOrder->company_id);

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'courier_name' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:500'],
            'total_packages' => ['nullable', 'integer', 'min:1'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_detail_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:0'],
        ]);

        $deliveryNote = DB::transaction(function () use ($request, $validated, $salesOrder) {
            $locked = SalesOrder::query()->lockForUpdate()->findOrFail($salesOrder->id);

            if ($locked->status === SalesOrder::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['sales_order' => 'Sales Order ini sudah dibatalkan.']);
            }

            $selectedItems = collect($validated['items'])->filter(fn ($item) => (int) $item['qty'] > 0)->values();
            if ($selectedItems->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Pilih minimal satu item dengan qty lebih dari 0.']);
            }

            $details = $locked->details()->whereIn('id', $selectedItems->pluck('sales_order_detail_id'))->get()->keyBy('id');

            $dnDetailsData = [];
            $totalWeight = 0;

            foreach ($selectedItems as $item) {
                $detail = $details->get((int) $item['sales_order_detail_id']);
                if (! $detail) {
                    throw ValidationException::withMessages(['items' => 'Item Sales Order tidak ditemukan.']);
                }

                $qty = (int) $item['qty'];
                $remaining = $detail->remainingToReserve();
                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => 'Qty untuk "'.$detail->product_name.'" melebihi sisa qty yang belum dikirim ('.$remaining.').']);
                }

                $variant = ProductVariant::find($detail->product_variant_id);
                $totalWeight += (int) ($variant?->weight_grams ?? 0) * $qty;

                $dnDetailsData[] = [
                    'sales_order_detail_id' => $detail->id,
                    'product_variant_id' => $detail->product_variant_id,
                    'product_name' => $detail->product_name,
                    'variant_name' => $detail->variant_name,
                    'sku' => $detail->sku,
                    'quantity' => $qty,
                ];
            }

            $companyId = $locked->company_id;

            $deliveryNote = DeliveryNote::create([
                'company_id' => $companyId,
                'delivery_note_no' => $this->documentNumberGenerator->generate(DeliveryNote::class, 'SJ', $companyId),
                'sales_order_id' => $locked->id,
                'status' => DeliveryNote::STATUS_DRAFT,
                'recipient_name' => $validated['recipient_name'],
                'shipping_address' => $validated['shipping_address'],
                'courier_name' => $validated['courier_name'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by_user_id' => $request->user()?->id,
            ]);

            foreach ($dnDetailsData as $data) {
                $deliveryNote->details()->create($data);
            }

            PackingList::create([
                'company_id' => $companyId,
                'packing_list_no' => $this->documentNumberGenerator->generate(PackingList::class, 'PL', $companyId),
                'delivery_note_id' => $deliveryNote->id,
                'total_weight_grams' => $totalWeight,
                'total_packages' => $validated['total_packages'] ?? null,
            ]);

            return $deliveryNote;
        });

        return redirect()->route('delivery-notes.show', $deliveryNote)->with('success', 'Surat Jalan & Packing List berhasil dibuat (draft). Konfirmasi pengiriman untuk memotong stok.');
    }

    public function show(DeliveryNote $deliveryNote)
    {
        $this->guardCompanyOwnership($deliveryNote->company_id);

        $deliveryNote->load(['details', 'packingList', 'salesOrder', 'createdByUser']);

        return view('backend.delivery-notes.show', compact('deliveryNote'));
    }

    public function ship(Request $request, DeliveryNote $deliveryNote)
    {
        $this->guardCompanyOwnership($deliveryNote->company_id);

        DB::transaction(function () use ($request, $deliveryNote) {
            $locked = DeliveryNote::query()->lockForUpdate()->findOrFail($deliveryNote->id);

            if ($locked->status !== DeliveryNote::STATUS_DRAFT) {
                throw ValidationException::withMessages(['delivery_note' => 'Surat Jalan ini sudah dikonfirmasi atau dibatalkan.']);
            }

            $details = $locked->details;

            foreach ($details as $detail) {
                $variant = ProductVariant::query()->lockForUpdate()->find($detail->product_variant_id);
                if (! $variant) {
                    throw ValidationException::withMessages(['items' => 'Produk "'.$detail->product_name.'" tidak ditemukan.']);
                }

                $stockBefore = (int) $variant->stock;
                if ($stockBefore < $detail->quantity) {
                    throw ValidationException::withMessages(['items' => 'Stok "'.$detail->product_name.'" tidak mencukupi ('.$stockBefore.' tersedia, butuh '.$detail->quantity.'). Koordinasikan ulang dengan sales.']);
                }

                $stockAfter = $stockBefore - $detail->quantity;
                $variant->stock = $stockAfter;
                $variant->save();

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'transaction_detail_id' => null,
                    'admin_user_id' => $request->user()?->id,
                    'type' => 'out',
                    'quantity' => $detail->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'source' => 'delivery_note',
                    'description' => 'Surat Jalan '.$locked->delivery_note_no,
                ]);
            }

            $locked->update([
                'status' => DeliveryNote::STATUS_SHIPPED,
                'shipped_at' => now(),
            ]);

            $locked->salesOrder->recomputeFulfillmentStatus();
        });

        return back()->with('success', 'Surat Jalan dikonfirmasi terkirim, stok berhasil dipotong.');
    }

    public function markDelivered(DeliveryNote $deliveryNote)
    {
        $this->guardCompanyOwnership($deliveryNote->company_id);

        if ($deliveryNote->status !== DeliveryNote::STATUS_SHIPPED) {
            throw ValidationException::withMessages(['delivery_note' => 'Hanya Surat Jalan berstatus shipped yang bisa ditandai delivered.']);
        }

        $deliveryNote->update([
            'status' => DeliveryNote::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);

        return back()->with('success', 'Surat Jalan ditandai sudah diterima customer.');
    }

    public function cancel(Request $request, DeliveryNote $deliveryNote)
    {
        $this->guardCompanyOwnership($deliveryNote->company_id);

        if (! $deliveryNote->canBeCancelled()) {
            throw ValidationException::withMessages(['delivery_note' => 'Surat Jalan hanya bisa dibatalkan selagi masih draft (belum dikonfirmasi terkirim).']);
        }

        $deliveryNote->update([
            'status' => DeliveryNote::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancelled_by_user_id' => $request->user()?->id,
        ]);

        return back()->with('success', 'Surat Jalan berhasil dibatalkan.');
    }

    public function print(DeliveryNote $deliveryNote)
    {
        $this->guardCompanyOwnership($deliveryNote->company_id);

        $deliveryNote->load(['details', 'packingList', 'salesOrder', 'company']);

        return view('backend.delivery-notes.print', compact('deliveryNote'));
    }
}
