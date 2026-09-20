<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $fillable = ['transaction_id', 'coupon_id', 'discount_amount', 'status', 'expires_at', 'redeemed_at', 'released_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'released_at' => 'datetime',
    ];
}
