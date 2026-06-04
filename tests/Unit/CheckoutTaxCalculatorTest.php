<?php

namespace Tests\Unit;

use App\Services\CheckoutTaxCalculator;
use PHPUnit\Framework\TestCase;

class CheckoutTaxCalculatorTest extends TestCase
{
    public function test_it_calculates_tax_after_discount_without_taxing_shipping(): void
    {
        $result = (new CheckoutTaxCalculator())->calculate(1_000_000, 100_000, 25_000, [
            'enabled' => true,
            'name' => 'PPN',
            'rate' => 11,
        ]);

        $this->assertSame(900_000, $result['taxable_amount']);
        $this->assertSame(99_000, $result['tax_amount']);
        $this->assertSame(1_024_000, $result['grand_total']);
    }

    public function test_it_returns_zero_tax_when_disabled(): void
    {
        $result = (new CheckoutTaxCalculator())->calculate(1_000_000, 100_000, 25_000, [
            'enabled' => false,
            'name' => 'PPN',
            'rate' => 11,
        ]);

        $this->assertSame(900_000, $result['taxable_amount']);
        $this->assertSame(0, $result['tax_amount']);
        $this->assertSame(925_000, $result['grand_total']);
    }
}
