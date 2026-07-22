<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_PARTIALLY_CONVERTED = 'partially_converted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CLOSED = 'closed';

    public const READ_ONLY_STATUSES = [self::STATUS_CLOSED, self::STATUS_REJECTED, self::STATUS_EXPIRED];

    protected $fillable = [
        'company_id',
        'quotation_no',
        'user_id',
        'manual_customer_name',
        'manual_customer_phone',
        'manual_customer_email',
        'status',
        'subtotal_amount',
        'discount_amount',
        'ppn_rate',
        'ppn_amount',
        'shipping_cost',
        'admin_fee',
        'other_cost',
        'other_cost_note',
        'grand_total',
        'valid_until',
        'note',
        'created_by_admin_id',
        'closed_at',
        'closed_by_admin_id',
        'close_reason',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'closed_at' => 'datetime',
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'ppn_rate' => 'float',
        'ppn_amount' => 'integer',
        'shipping_cost' => 'integer',
        'admin_fee' => 'integer',
        'other_cost' => 'integer',
        'grand_total' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function closedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_admin_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuotationDetail::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(QuotationStatusHistory::class)->orderBy('created_at');
    }

    public function customerName(): string
    {
        return $this->user?->name ?: (string) ($this->manual_customer_name ?: '-');
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, self::READ_ONLY_STATUSES, true);
    }

    public function isExpiredByDate(): bool
    {
        return $this->valid_until !== null && $this->valid_until->lt(now()->startOfDay());
    }

    /**
     * Number of Sales Orders ever created from this Quotation (including cancelled
     * ones — a cancelled Sales Order still counts as "at least one was ever made").
     */
    public function salesOrdersCount(): int
    {
        return $this->salesOrders()->count();
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    /**
     * Whether item-level fields (products/qty/price) can still be edited. Once at
     * least one Sales Order has been drawn from this Quotation, items are locked to
     * protect quotation_detail_id references already snapshotted downstream — only
     * valid_until/note remain editable via the same form.
     */
    public function itemsAreEditable(): bool
    {
        return ! $this->isReadOnly() && $this->salesOrdersCount() === 0;
    }
}
