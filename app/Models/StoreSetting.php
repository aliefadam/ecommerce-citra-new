<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function defaults(): array
    {
        return [
            'store_name' => 'Ecommerce Citra',
            'store_logo_path' => '',
            'manual_payment_bank_name' => 'BCA',
            'manual_payment_account_number' => '1234567890',
            'manual_payment_account_name' => 'Ecommerce Citra',
            'manual_payment_instruction' => 'Transfer sesuai nominal pesanan, lalu upload bukti pembayaran agar admin bisa memverifikasi.',
            'tax_enabled' => '1',
            'tax_name' => 'PPN',
            'tax_rate' => '11.00',
            'social_instagram' => '',
            'social_tiktok' => '',
            'social_facebook' => '',
            'social_twitter' => '',
            'social_youtube' => '',
            'social_whatsapp' => '',
            'social_shopee' => '',
            'social_tokopedia' => '',
            'social_lazada' => '',
            'wa_gateway_store_id' => 'boq-ecommerce',
            'wa_gateway_per_minute' => '10',
            'wa_gateway_per_day' => '200',
            'wa_gateway_per_month' => '3000',
        ];
    }

    public static function values(): array
    {
        return array_merge(
            static::defaults(),
            static::query()->pluck('value', 'key')->all()
        );
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    public static function manualPayment(): array
    {
        $settings = static::values();

        return [
            'bank_name' => (string) $settings['manual_payment_bank_name'],
            'account_number' => (string) $settings['manual_payment_account_number'],
            'account_name' => (string) $settings['manual_payment_account_name'],
            'instruction' => (string) $settings['manual_payment_instruction'],
        ];
    }

    public static function taxSettings(): array
    {
        $settings = static::values();

        return [
            'enabled' => filter_var($settings['tax_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'name' => (string) ($settings['tax_name'] ?? 'PPN'),
            'rate' => (float) ($settings['tax_rate'] ?? 11),
        ];
    }
}
