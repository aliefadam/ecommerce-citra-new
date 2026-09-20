<?php

namespace App\Models;

use App\Models\Concerns\DefaultsToPrimaryCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use DefaultsToPrimaryCompany;

    public const TYPES = [
        'motor' => 'Motor',
        'mobil' => 'Mobil',
        'van' => 'Van',
        'pickup' => 'Pickup',
        'truk' => 'Truk',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'plate_number',
        'capacity_kg',
        'rate_per_kg',
        'distance_block_km',
        'rate_per_distance_block',
        'is_active',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'capacity_kg' => 'integer',
            'rate_per_kg' => 'integer',
            'distance_block_km' => 'decimal:2',
            'rate_per_distance_block' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'shipping_vehicle_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function calculateShippingCost(int $weightGrams, float $distanceKm = 0): int
    {
        $breakdown = $this->calculateShippingBreakdown($weightGrams, $distanceKm);

        return $breakdown['total'];
    }

    public function calculateShippingBreakdown(int $weightGrams, float $distanceKm): array
    {
        $weightCost = $weightGrams > 0
            ? (int) ceil($weightGrams / 1000) * (int) $this->rate_per_kg
            : 0;
        $distanceBlockKm = max(0.01, (float) $this->distance_block_km);
        $distanceCost = $distanceKm > 0
            ? (int) ceil($distanceKm / $distanceBlockKm) * (int) $this->rate_per_distance_block
            : 0;

        return [
            'weight_cost' => $weightCost,
            'distance_cost' => $distanceCost,
            'total' => $weightCost + $distanceCost,
        ];
    }
}
