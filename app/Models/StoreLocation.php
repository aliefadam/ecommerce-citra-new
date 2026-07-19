<?php

namespace App\Models;

use App\Models\Concerns\DefaultsToPrimaryCompany;
use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    use DefaultsToPrimaryCompany;

    protected $fillable = [
        'company_id',
        'label',
        'province_id',
        'city_id',
        'city_name',
        'province_name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'province_id' => 'integer',
            'city_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
