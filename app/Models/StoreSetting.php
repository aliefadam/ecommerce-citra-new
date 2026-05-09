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
            'manual_payment_bank_name' => 'BCA',
            'manual_payment_account_number' => '1234567890',
            'manual_payment_account_name' => 'Ecommerce Citra',
            'manual_payment_instruction' => 'Transfer sesuai nominal pesanan, lalu upload bukti pembayaran agar admin bisa memverifikasi.',
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
}
