<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecificationTemplateField extends Model
{
    protected $fillable = [
        'specification_template_id', 'attribute_definition_id', 'label_override', 'input_type',
        'is_required', 'is_filterable', 'affects_variant', 'allow_custom_value', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean', 'is_filterable' => 'boolean', 'affects_variant' => 'boolean',
        'allow_custom_value' => 'boolean', 'is_active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(SpecificationTemplate::class, 'specification_template_id');
    }

    public function definition()
    {
        return $this->belongsTo(AttributeDefinition::class, 'attribute_definition_id');
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class, 'attribute_definition_id', 'attribute_definition_id')
            ->where('specification_template_id', $this->specification_template_id)
            ->orderBy('sort_order')->orderBy('value');
    }

    public function label(): string
    {
        return (string) ($this->label_override ?: $this->definition?->name ?: 'Atribut');
    }
}
