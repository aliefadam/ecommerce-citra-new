<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionProductReview extends Model
{
    protected $fillable = [
        'transaction_id',
        'transaction_detail_id',
        'user_id',
        'rating',
        'message',
        'photos',
        'is_hidden',
        'hidden_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_hidden' => 'boolean',
        'hidden_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function transactionDetail(): BelongsTo
    {
        return $this->belongsTo(TransactionDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
