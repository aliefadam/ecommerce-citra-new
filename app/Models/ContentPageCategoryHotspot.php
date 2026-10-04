<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPageCategoryHotspot extends Model
{
    protected $fillable = [
        'main_category_id',
        'category_detail_id',
        'x_percent',
        'y_percent',
    ];

    protected $casts = [
        'x_percent' => 'float',
        'y_percent' => 'float',
    ];

    public function contentPage(): BelongsTo
    {
        return $this->belongsTo(ContentPage::class);
    }

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function categoryDetail(): BelongsTo
    {
        return $this->belongsTo(CategoryDetail::class);
    }

    public function getTargetCategoryAttribute(): MainCategory|CategoryDetail|null
    {
        return $this->categoryDetail ?: $this->mainCategory;
    }

    public function getTargetKeyAttribute(): string
    {
        return $this->category_detail_id
            ? 'detail:'.$this->category_detail_id
            : 'main:'.$this->main_category_id;
    }
}
