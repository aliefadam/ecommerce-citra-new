<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'logo_path',
        'address',
        'phone',
        'email',
        'npwp',
        'invoice_prefix',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function flashSales(): HasMany
    {
        return $this->hasMany(FlashSale::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function storeLocations(): HasMany
    {
        return $this->hasMany(StoreLocation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function banners(): HasMany
    {
        return $this->hasMany(Banner::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function paymentCredentials(): HasMany
    {
        return $this->hasMany(CompanyPaymentCredential::class);
    }

    public function adminAssignments(): HasMany
    {
        return $this->hasMany(AdminCompanyAssignment::class);
    }
}
