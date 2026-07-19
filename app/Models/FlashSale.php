<?php

namespace App\Models;

use App\Models\Concerns\DefaultsToPrimaryCompany;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use DefaultsToPrimaryCompany;

    protected $fillable = [
        'company_id',
        'name',
        'start_at',
        'end_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(FlashSaleItem::class);
    }
}

