<?php

namespace App\Services;

/**
 * Shared PPN + biaya lain (ongkir/admin/lain-lain) calculation for Quotation,
 * Sales Order, Proforma Invoice, dan Invoice B2B — supaya rumus grand_total
 * konsisten di keempat dokumen (lihat PRD §Update 2026-07-22 v2).
 */
class DocumentFinancials
{
    public static function compute(int $subtotal, int $discount, float $ppnRate, int $shippingCost, int $adminFee, int $otherCost): array
    {
        $taxable = max(0, $subtotal - $discount);
        $ppnAmount = (int) round($taxable * $ppnRate / 100);
        $grandTotal = $taxable + $ppnAmount + $shippingCost + $adminFee + $otherCost;

        return [
            'ppn_rate' => $ppnRate,
            'ppn_amount' => $ppnAmount,
            'shipping_cost' => $shippingCost,
            'admin_fee' => $adminFee,
            'other_cost' => $otherCost,
            'grand_total' => $grandTotal,
        ];
    }

    public static function defaultPpnRate(int $companyId): float
    {
        $tax = \App\Models\CompanySetting::taxSettings($companyId);

        return $tax['enabled'] ? (float) $tax['rate'] : 0.0;
    }
}
