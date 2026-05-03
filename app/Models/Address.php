<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone_country_code',
        'phone_number',
        'province_id',
        'city_id',
        'district_id',
        'subdistrict_id',
        'province',
        'city',
        'district',
        'subdistrict',
        'postal_code',
        'destination_id',
        'address_line',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
