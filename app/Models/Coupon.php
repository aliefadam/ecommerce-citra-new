<?php

namespace App\Models;

use App\Models\Concerns\DefaultsToPrimaryCompany;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use DefaultsToPrimaryCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'value',
        'max_discount',
        'min_purchase',
        'usage_limit',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isUsableFor(int $subtotal): bool
    {
        if (!$this->is_active || $subtotal < (int) $this->min_purchase) {
            return false;
        }

        if ($this->usage_limit !== null && (int) $this->used_count >= (int) $this->usage_limit) {
            return false;
        }

        $now = now();
        if ($this->starts_at && $this->starts_at->greaterThan($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lessThan($now)) {
            return false;
        }

        return true;
    }

    public function discountFor(int $subtotal): int
    {
        if (!$this->isUsableFor($subtotal)) {
            return 0;
        }

        $discount = $this->type === 'percent'
            ? (int) floor($subtotal * ((int) $this->value / 100))
            : (int) $this->value;

        if ($this->max_discount !== null) {
            $discount = min($discount, (int) $this->max_discount);
        }

        return max(0, min($subtotal, $discount));
    }
}
