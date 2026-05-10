<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'category_id', 'main_category_id', 'category_detail_id', 'description', 'is_redeem_product', 'redeem_points', 'status'];

    protected $casts = [
        'is_redeem_product' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function mainCategory()
    {
        return $this->belongsTo(MainCategory::class);
    }

    public function categoryDetail()
    {
        return $this->belongsTo(CategoryDetail::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function flashSaleItems()
    {
        return $this->hasManyThrough(FlashSaleItem::class, ProductVariant::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }
}
