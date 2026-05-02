<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashSaleItem extends Model
{
    protected $fillable = [
        'flash_sale_id',
        'product_variant_id',
        'discount_price',
        'quota',
        'sold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function flashSale()
    {
        return $this->belongsTo(FlashSale::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}

