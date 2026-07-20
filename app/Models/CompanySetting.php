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

    /**
     * manual_payment_* default kosong (bukan mewarisi data BOQ) supaya perusahaan baru wajib
     * diisi eksplisit dulu sebelum bisa menerima transfer manual -- lihat
     * docs/prd-multi-company-foundation.md §4b. tax_* default ke kebijakan umum (PPN 11%) supaya
     * perusahaan baru tidak otomatis 0% pajak kalau belum sempat dikonfigurasi.
     */
    public static function defaults(): array
    {
        return [
            'manual_payment_bank_name' => '',
            'manual_payment_account_number' => '',
            'manual_payment_account_name' => '',
            'manual_payment_instruction' => '',
            'tax_enabled' => '1',
            'tax_name' => 'PPN',
            'tax_rate' => '11.00',
        ];
    }

    public static function valuesWithDefaults(int $companyId): array
    {
        return array_merge(static::defaults(), static::values($companyId));
    }

    public static function manualPayment(int $companyId): array
    {
        $settings = static::valuesWithDefaults($companyId);

        return [
            'bank_name' => (string) $settings['manual_payment_bank_name'],
            'account_number' => (string) $settings['manual_payment_account_number'],
            'account_name' => (string) $settings['manual_payment_account_name'],
            'instruction' => (string) $settings['manual_payment_instruction'],
        ];
    }

    public static function isManualPaymentConfigured(int $companyId): bool
    {
        $payment = static::manualPayment($companyId);

        return $payment['bank_name'] !== '' && $payment['account_number'] !== '' && $payment['account_name'] !== '';
    }

    public static function taxSettings(int $companyId): array
    {
        $settings = static::valuesWithDefaults($companyId);

        return [
            'enabled' => filter_var($settings['tax_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'name' => (string) ($settings['tax_name'] ?? 'PPN'),
            'rate' => (float) ($settings['tax_rate'] ?? 11),
        ];
    }
}
