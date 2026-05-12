<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantAttribute extends Model
{
    protected $fillable = [
        'product_variant_id',
        'attribute_definition_id',
        'value_text',
        'value_number',
    ];

    protected $casts = [
        'value_number' => 'decimal:3',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function definition()
    {
        return $this->belongsTo(AttributeDefinition::class, 'attribute_definition_id');
    }
}
