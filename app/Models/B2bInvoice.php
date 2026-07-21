<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class B2bInvoice extends Model
{
    public const STATUS_ISSUED = 'issued';
    public const STATUS_PARTIALLY_PAID = 'partially_paid';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'b2b_invoice_no',
        'sales_order_id',
        'user_id',
        'manual_customer_name',
        'manual_customer_phone',
        'manual_customer_email',
        'status',
        'subtotal_amount',
        'grand_total',
        'paid_amount',
        'outstanding_amount',
        'due_date',
        'issued_at',
        'created_by_admin_id',
        'cancelled_by_admin_id',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'integer',
        'grand_total' => 'integer',
        'paid_amount' => 'integer',
        'outstanding_amount' => 'integer',
        'due_date' => 'date',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function cancelledByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_admin_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(B2bInvoiceDetail::class);
    }

    public function deliveryNotes(): BelongsToMany
    {
        return $this->belongsToMany(DeliveryNote::class, 'b2b_invoice_delivery_note');
    }

    public function documentPayments(): MorphMany
    {
        return $this->morphMany(DocumentPayment::class, 'payable')->orderBy('payment_date');
    }

    public function customerName(): string
    {
        return $this->user?->name ?: (string) ($this->manual_customer_name ?: '-');
    }

    public function canBeCancelled(): bool
    {
        return $this->status !== self::STATUS_CANCELLED && $this->paid_amount <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && $this->due_date->lt(now()->startOfDay())
            && in_array($this->status, [self::STATUS_ISSUED, self::STATUS_PARTIALLY_PAID], true);
    }
}
