<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryDetail extends Model
{
    protected $fillable = ['main_category_id', 'specification_template_id', 'name', 'slug'];

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function specificationTemplate(): BelongsTo
    {
        return $this->belongsTo(SpecificationTemplate::class);
    }
}
