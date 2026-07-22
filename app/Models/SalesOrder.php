<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PARTIALLY_FULFILLED = 'partially_fulfilled';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'sales_order_no',
        'quotation_id',
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
        'created_by_admin_id',
        'cancelled_by_admin_id',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal_amount' => 'integer',
        'discount_amount' => 'integer',
        'ppn_rate' => 'float',
        'ppn_amount' => 'integer',
        'shipping_cost' => 'integer',
        'admin_fee' => 'integer',
        'other_cost' => 'integer',
        'grand_total' => 'integer',
        'cancelled_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
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
        return $this->hasMany(SalesOrderDetail::class);
    }

    public function proformaInvoices(): HasMany
    {
        return $this->hasMany(ProformaInvoice::class);
    }

    /**
     * MVP restriction: only one active (non-cancelled) Proforma Invoice per Sales Order.
     */
    public function hasActiveProformaInvoice(): bool
    {
        return $this->proformaInvoices()->where('status', '!=', ProformaInvoice::STATUS_CANCELLED)->exists();
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(SalesOrderStatusHistory::class)->orderBy('created_at');
    }

    public function customerName(): string
    {
        return $this->user?->name ?: (string) ($this->manual_customer_name ?: '-');
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function b2bInvoices(): HasMany
    {
        return $this->hasMany(B2bInvoice::class);
    }

    /**
     * All shipped/delivered Delivery Notes not yet linked to any active B2B Invoice —
     * these are the candidates offered when creating a new Invoice B2B. A Delivery
     * Note whose only linked invoice was cancelled must be billable again.
     */
    public function uninvoicedDeliveryNotes()
    {
        return $this->deliveryNotes()
            ->whereIn('status', [DeliveryNote::STATUS_SHIPPED, DeliveryNote::STATUS_DELIVERED])
            ->whereDoesntHave('b2bInvoices', fn ($q) => $q->where('status', '!=', B2bInvoice::STATUS_CANCELLED))
            ->get();
    }

    public function isFullyInvoiced(): bool
    {
        return $this->uninvoicedDeliveryNotes()->isEmpty()
            && $this->deliveryNotes()->whereIn('status', [DeliveryNote::STATUS_SHIPPED, DeliveryNote::STATUS_DELIVERED])->exists();
    }

    /**
     * Earliest still-active Invoice issued via the "direct from Sales Order" path
     * that doesn't have a Delivery Note attached yet — used to auto-attach a newly
     * created Delivery Note to it. Excluding invoices that already have one keeps
     * multiple direct invoices on the same Sales Order each getting exactly one
     * Delivery Note attached in FIFO order (see PRD §5 Behavior), instead of all
     * new Delivery Notes piling onto the very first invoice.
     */
    public function firstActiveDirectInvoice(): ?B2bInvoice
    {
        return $this->b2bInvoices()
            ->where('source', B2bInvoice::SOURCE_DIRECT)
            ->where('status', '!=', B2bInvoice::STATUS_CANCELLED)
            ->whereDoesntHave('deliveryNotes')
            ->oldest()
            ->first();
    }

    /**
     * Number of active (non-cancelled) Delivery Notes ever created from this Sales
     * Order. Gates the Sales Order cancel action.
     */
    public function deliveryNotesCount(): int
    {
        return $this->deliveryNotes()->where('status', '!=', DeliveryNote::STATUS_CANCELLED)->count();
    }

    public function canBeCancelled(): bool
    {
        return $this->status !== self::STATUS_CANCELLED && $this->deliveryNotesCount() < 1;
    }

    /**
     * Recompute confirmed/partially_fulfilled/fulfilled based on shipped qty across
     * all details. Called after a Delivery Note is shipped/cancelled.
     */
    public function recomputeFulfillmentStatus(): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        $this->load('details');
        $totalQty = $this->details->sum('quantity');
        $totalShipped = $this->details->sum(fn (SalesOrderDetail $detail) => $detail->quantityShipped());

        $status = match (true) {
            $totalShipped <= 0 => self::STATUS_CONFIRMED,
            $totalShipped >= $totalQty => self::STATUS_FULFILLED,
            default => self::STATUS_PARTIALLY_FULFILLED,
        };

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }
}
