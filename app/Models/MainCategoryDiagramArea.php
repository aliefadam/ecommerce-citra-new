<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MainCategoryDiagramArea extends Model
{
    protected $fillable = [
        'category_detail_id',
        'x_percent',
        'y_percent',
        'width_percent',
        'height_percent',
    ];

    protected $casts = [
        'x_percent' => 'float',
        'y_percent' => 'float',
        'width_percent' => 'float',
        'height_percent' => 'float',
    ];

    public function mainCategory(): BelongsTo
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function categoryDetail(): BelongsTo
    {
        return $this->belongsTo(CategoryDetail::class);
    }
}
