<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttributeOption extends Model
{
    protected $fillable = ['attribute_definition_id', 'specification_template_id', 'value', 'label', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function definition()
    {
        return $this->belongsTo(AttributeDefinition::class, 'attribute_definition_id');
    }

    public function template()
    {
        return $this->belongsTo(SpecificationTemplate::class, 'specification_template_id');
    }
}
