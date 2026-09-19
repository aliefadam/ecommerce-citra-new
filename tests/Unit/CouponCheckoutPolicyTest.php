<?php

namespace Tests\Unit;

use Tests\TestCase;

class CouponCheckoutPolicyTest extends TestCase
{
    public function test_checkout_controllers_do_not_increment_coupon_usage_directly(): void
    {
        $controllers = [
            app_path('Http/Controllers/ManualPaymentController.php'),
            app_path('Http/Controllers/MidtransController.php'),
        ];

        foreach ($controllers as $controller) {
            $source = file_get_contents($controller);

            $this->assertIsString($source);
            $this->assertStringNotContainsString(
                "increment('used_count')",
                $source,
                basename($controller).' masih memakai pola check-then-increment yang rentan race condition.'
            );
        }
    }
}
