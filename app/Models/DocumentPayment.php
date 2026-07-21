<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentPayment extends Model
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_DP_CREDIT = 'dp_credit';

    protected $fillable = [
        'payable_type',
        'payable_id',
        'amount',
        'payment_date',
        'note',
        'proof_path',
        'source',
        'recorded_by_admin_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'payment_date' => 'date',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function recordedByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_admin_id');
    }
}
