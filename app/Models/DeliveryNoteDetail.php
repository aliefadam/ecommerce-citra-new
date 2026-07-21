<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteDetail extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'sales_order_detail_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'sku',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function salesOrderDetail(): BelongsTo
    {
        return $this->belongsTo(SalesOrderDetail::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
