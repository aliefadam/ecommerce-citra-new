<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationDetail extends Model
{
    protected $fillable = [
        'quotation_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'image',
        'original_price',
        'price',
        'quantity',
        'subtotal',
        'item_note',
    ];

    protected $casts = [
        'original_price' => 'integer',
        'price' => 'integer',
        'quantity' => 'integer',
        'subtotal' => 'integer',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
     * Qty already pulled into (non-cancelled) Sales Orders derived from this line.
     * Cancelled Sales Orders don't count, so their qty returns to the drawable pool.
     */
    public function quantityConverted(): int
    {
        return (int) SalesOrderDetail::query()
            ->where('quotation_detail_id', $this->id)
            ->whereHas('salesOrder', fn ($q) => $q->where('status', '!=', SalesOrder::STATUS_CANCELLED))
            ->sum('quantity');
    }

    public function remainingQuantity(): int
    {
        return max(0, (int) $this->quantity - $this->quantityConverted());
    }
}
