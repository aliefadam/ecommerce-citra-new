<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainCategory extends Model
{
    protected $fillable = ['name', 'slug', 'image', 'diagram_image', 'default_specification_template_id'];

    public function categoryDetails(): HasMany
    {
        return $this->hasMany(CategoryDetail::class);
    }

    public function diagramAreas(): HasMany
    {
        return $this->hasMany(MainCategoryDiagramArea::class)->orderBy('id');
    }

    public function defaultSpecificationTemplate()
    {
        return $this->belongsTo(SpecificationTemplate::class, 'default_specification_template_id');
    }
}
