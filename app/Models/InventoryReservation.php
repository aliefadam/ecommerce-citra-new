<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
{
    protected $fillable = ['transaction_id', 'product_variant_id', 'quantity', 'status', 'expires_at', 'committed_at', 'released_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'committed_at' => 'datetime',
        'released_at' => 'datetime',
    ];
}
