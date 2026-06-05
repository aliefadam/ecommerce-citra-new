<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTaxProfile extends Model
{
    protected $fillable = [
        'user_id',
        'taxpayer_name',
        'taxpayer_number',
        'taxpayer_address',
        'taxpayer_email',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getMaskedTaxpayerNumberAttribute(): string
    {
        return TransactionTaxInvoice::maskTaxpayerNumber($this->taxpayer_number);
    }

    public function setTaxpayerNumberAttribute(?string $value): void
    {
        $this->attributes['taxpayer_number'] = TransactionTaxInvoice::normalizeTaxpayerNumber($value);
    }
}
