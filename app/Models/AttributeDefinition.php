<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeDefinition extends Model
{
    protected $fillable = [
        'code',
        'name',
        'data_type',
        'unit',
        'is_filterable',
        'sort_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
    ];

    public function productVariantAttributes()
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }
}
