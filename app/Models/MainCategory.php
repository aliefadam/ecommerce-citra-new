<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MainCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    public function categoryDetails(): HasMany
    {
        return $this->hasMany(CategoryDetail::class);
    }
}
