<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionDetail extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'image',
        'price',
        'quantity',
        'subtotal',
        'item_note',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(TransactionProductReview::class);
    }

    public function returnRequestItems(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }
}
