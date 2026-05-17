<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    public const TYPE_PAGE = 'page';
    public const TYPE_POST = 'post';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'excerpt',
        'content',
        'hero_image',
        'meta_title',
        'meta_description',
        'is_active',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function getPublicUrlAttribute(): string
    {
        return $this->type === self::TYPE_POST
            ? route('frontend.blog.show', $this->slug)
            : route('frontend.pages.show', $this->slug);
    }
}
