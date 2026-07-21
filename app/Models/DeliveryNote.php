<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryNote extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'delivery_note_no',
        'sales_order_id',
        'status',
        'recipient_name',
        'shipping_address',
        'courier_name',
        'note',
        'created_by_user_id',
        'shipped_at',
        'delivered_at',
        'cancelled_by_user_id',
        'cancelled_at',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
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

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeliveryNoteDetail::class);
    }

    public function packingList(): HasOne
    {
        return $this->hasOne(PackingList::class);
    }

    public function b2bInvoices(): BelongsToMany
    {
        return $this->belongsToMany(B2bInvoice::class, 'b2b_invoice_delivery_note');
    }

    public function canBeCancelled(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
