<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class B2bInvoiceDetail extends Model
{
    protected $fillable = [
        'b2b_invoice_id',
        'delivery_note_detail_id',
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

    public function b2bInvoice(): BelongsTo
    {
        return $this->belongsTo(B2bInvoice::class);
    }

    public function deliveryNoteDetail(): BelongsTo
    {
        return $this->belongsTo(DeliveryNoteDetail::class);
    }
}
