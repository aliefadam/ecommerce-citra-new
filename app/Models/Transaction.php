<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    public const SOURCE_CHECKOUT = 'checkout';

    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'user_id',
        'source',
        'created_by_admin_id',
        'manual_customer_name',
        'manual_customer_phone',
        'manual_customer_email',
        'invoice_no',
        'order_id',
        'midtrans_transaction_id',
        'payment_type',
        'payment_method',
        'payment_status',
        'payment_paid_at',
        'payment_amount',
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
        'shipping_type',
        'coupon_code',
        'discount_amount',
        'tax_name',
        'tax_rate',
        'taxable_amount',
        'tax_amount',
        'redeem_points_reserved',
        'redeem_points_reserved_at',
        'redeem_points_finalized_at',
        'redeem_points_released_at',
        'grand_total',
        'shipping_label',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_address_line',
        'shipping_city',
        'shipping_district',
        'shipping_province',
        'shipping_postal_code',
        'shipping_courier_name',
        'shipping_service',
        'tracking_number',
        'shipping_note',
        'processed_at',
        'shipped_at',
        'paid_at',
        'points_awarded_at',
        'expires_at',
        'cancel_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'paid_at' => 'datetime',
        'payment_paid_at' => 'datetime',
        'points_awarded_at' => 'datetime',
        'payment_proof_uploaded_at' => 'datetime',
        'payment_verified_at' => 'datetime',
        'payment_rejected_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'redeem_points_reserved_at' => 'datetime',
        'redeem_points_finalized_at' => 'datetime',
        'redeem_points_released_at' => 'datetime',
        'tax_rate' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction) {
            if (blank($transaction->source)) {
                $transaction->source = self::SOURCE_CHECKOUT;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
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

    public function taxInvoice(): HasOne
    {
        return $this->hasOne(TransactionTaxInvoice::class);
    }

    public function getSourceLabelAttribute(): string
    {
        return match ($this->normalizedSource()) {
            self::SOURCE_MANUAL, 'admin' => 'Manual Admin',
            self::SOURCE_CHECKOUT, 'ecommerce' => 'Checkout Ecommerce',
            default => 'Checkout Ecommerce',
        };
    }

    public function normalizedSource(): string
    {
        return strtolower(trim((string) ($this->source ?: self::SOURCE_CHECKOUT)));
    }

    public function customerDisplayName(): string
    {
        return (string) ($this->user?->name ?: $this->manual_customer_name ?: '-');
    }

    public function customerDisplayEmail(): string
    {
        return (string) ($this->user?->email ?: $this->manual_customer_email ?: '-');
    }

    public function paymentStatusLabel(): string
    {
        return match (strtolower((string) ($this->payment_status ?: 'unpaid'))) {
            'paid' => 'Paid',
            'partial' => 'Partial',
            'cancelled' => 'Cancelled',
            default => 'Unpaid',
        };
    }

    public function shippingTypeLabel(): string
    {
        return match (strtolower((string) ($this->shipping_type ?: 'belum_ditentukan'))) {
            'dikirim' => 'Dikirim',
            'ambil_sendiri' => 'Ambil sendiri',
            'kurir_toko' => 'Kurir toko',
            'ekspedisi_manual' => 'Ekspedisi manual',
            'gratis_ongkir' => 'Gratis ongkir',
            default => 'Belum ditentukan',
        };
    }
}
