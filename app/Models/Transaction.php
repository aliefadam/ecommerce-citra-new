<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'invoice_no',
        'order_id',
        'midtrans_transaction_id',
        'payment_type',
        'payment_method',
        'status',
        'subtotal_amount',
        'shipping_cost',
        'grand_total',
        'shipping_label',
        'tracking_number',
        'processed_at',
        'shipped_at',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
