<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPaymentCredential extends Model
{
    protected $fillable = [
        'company_id',
        'provider',
        'server_key',
        'client_key',
        'is_production',
        'is_active',
    ];

    protected $casts = [
        'server_key' => 'encrypted',
        'client_key' => 'encrypted',
        'is_production' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
