<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreLocation extends Model
{
    protected $fillable = [
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
}
