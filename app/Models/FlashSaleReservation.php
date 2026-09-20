<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleReservation extends Model
{
    protected $fillable = ['transaction_id', 'flash_sale_item_id', 'quantity', 'status', 'expires_at', 'committed_at', 'released_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'committed_at' => 'datetime',
        'released_at' => 'datetime',
    ];
}
