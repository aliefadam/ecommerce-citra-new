<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointHistory extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'points',
        'balance_before',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
