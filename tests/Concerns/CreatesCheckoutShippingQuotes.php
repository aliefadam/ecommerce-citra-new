<?php

namespace Tests\Concerns;

use App\Services\CheckoutPricingService;
use App\Services\ShippingQuoteService;

trait CreatesCheckoutShippingQuotes
{
    protected function checkoutShippingQuote(
        array $items,
        int $companyId,
        int $destinationId,
        int $cost = 0,
        string $label = 'Test Shipping',
    ): string {
        $pricing = app(CheckoutPricingService::class)->resolve(
            $items,
            $companyId,
            session('checkout.source') === 'redeem_point',
        );

        return app(ShippingQuoteService::class)->issue(
            $companyId,
            $destinationId,
            $pricing['fingerprint'],
            $pricing['weight_grams'],
            $cost,
            $label,
        );
    }
}
