<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackingList extends Model
{
    protected $fillable = [
        'company_id',
        'packing_list_no',
        'delivery_note_id',
        'total_weight_grams',
        'total_packages',
    ];

    protected $casts = [
        'total_weight_grams' => 'integer',
        'total_packages' => 'integer',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
