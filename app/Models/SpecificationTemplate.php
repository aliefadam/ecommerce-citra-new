<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecificationTemplate extends Model
{
    protected $fillable = ['name', 'code', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function fields()
    {
        return $this->hasMany(SpecificationTemplateField::class)->orderBy('sort_order')->orderBy('id');
    }

    public function activeFields()
    {
        return $this->fields()->where('is_active', true);
    }

    public function categoryDetails()
    {
        return $this->hasMany(CategoryDetail::class);
    }

    public function options()
    {
        return $this->hasMany(AttributeOption::class)->orderBy('sort_order')->orderBy('value');
    }

    public function defaultMainCategories()
    {
        return $this->hasMany(MainCategory::class, 'default_specification_template_id');
    }
}
