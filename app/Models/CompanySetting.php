<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function values(int $companyId): array
    {
        return static::query()
            ->where('company_id', $companyId)
            ->get()
            ->mapWithKeys(fn (self $setting) => [$setting->key => $setting->value])
            ->all();
    }

    public static function setMany(int $companyId, array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['company_id' => $companyId, 'key' => $key],
                ['value' => $value]
            );
        }
    }
}
