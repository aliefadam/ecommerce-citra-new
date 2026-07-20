<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\StoreSetting;

class CheckoutTaxCalculator
{
    public function calculate(int|float $subtotal, int|float $discountAmount, int|float $shippingCost, ?array $settings = null, ?int $companyId = null): array
    {
        $settings = $settings ?? ($companyId ? CompanySetting::taxSettings($companyId) : StoreSetting::taxSettings());

        $subtotal = max(0, (int) round((float) $subtotal));
        $discountAmount = max(0, (int) round((float) $discountAmount));
        $shippingCost = max(0, (int) round((float) $shippingCost));
        $taxEnabled = (bool) ($settings['enabled'] ?? false);
        $taxRate = $taxEnabled ? max(0, min(100, (float) ($settings['rate'] ?? 0))) : 0.0;
        $taxName = $taxEnabled ? trim((string) ($settings['name'] ?? 'PPN')) : '';
        $taxableAmount = max(0, $subtotal - $discountAmount);
        $taxAmount = $taxEnabled ? (int) round($taxableAmount * $taxRate / 100) : 0;

        return [
            'taxable_amount' => $taxableAmount,
            'tax_name' => $taxEnabled ? ($taxName !== '' ? $taxName : 'PPN') : null,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'grand_total' => $taxableAmount + $taxAmount + $shippingCost,
        ];
    }
}
