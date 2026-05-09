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
        'payment_va_number',
        'payment_va_bank',
        'payment_qr_url',
        'payment_proof_path',
        'payment_proof_uploaded_at',
        'payment_verified_at',
        'payment_rejected_at',
        'payment_admin_note',
        'status',
        'subtotal_amount',
        'shipping_cost',
        'coupon_code',
        'discount_amount',
        'grand_total',
        'shipping_label',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_address_line',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'tracking_number',
        'shipping_note',
        'processed_at',
        'shipped_at',
        'paid_at',
        'expires_at',
        'cancel_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function productReviews(): HasMany
    {
        return $this->hasMany(TransactionProductReview::class);
    }

    public function returnRequests(): HasMany
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TransactionStatusHistory::class);
    }
}
