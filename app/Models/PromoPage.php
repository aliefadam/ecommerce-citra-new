<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'hero_image',
        'cta_label',
        'cta_url',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
