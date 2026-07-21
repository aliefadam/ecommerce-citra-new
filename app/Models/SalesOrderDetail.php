<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderDetail extends Model
{
    protected $fillable = [
        'sales_order_id',
        'quotation_detail_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'price',
        'quantity',
    ];

    protected $casts = [
        'price' => 'integer',
        'quantity' => 'integer',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function quotationDetail(): BelongsTo
    {
        return $this->belongsTo(QuotationDetail::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Qty actually shipped (Delivery Note status shipped/delivered) — used for the
     * customer-facing "sudah dikirim" progress column.
     */
    public function quantityShipped(): int
    {
        return (int) DeliveryNoteDetail::query()
            ->where('sales_order_detail_id', $this->id)
            ->whereHas('deliveryNote', fn ($q) => $q->whereIn('status', [DeliveryNote::STATUS_SHIPPED, DeliveryNote::STATUS_DELIVERED]))
            ->sum('quantity');
    }

    /**
     * Qty reserved by any non-cancelled Delivery Note (draft included) — used to
     * validate new Delivery Notes so two drafts can't both claim the same qty
     * before either is confirmed shipped.
     */
    public function quantityReservedOrShipped(): int
    {
        return (int) DeliveryNoteDetail::query()
            ->where('sales_order_detail_id', $this->id)
            ->whereHas('deliveryNote', fn ($q) => $q->where('status', '!=', DeliveryNote::STATUS_CANCELLED))
            ->sum('quantity');
    }

    public function remainingToShip(): int
    {
        return max(0, (int) $this->quantity - $this->quantityShipped());
    }

    /**
     * Remaining qty available to allocate into a NEW Delivery Note (accounts for
     * other drafts already reserving qty, not just confirmed shipments).
     */
    public function remainingToReserve(): int
    {
        return max(0, (int) $this->quantity - $this->quantityReservedOrShipped());
    }
}
