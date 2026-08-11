<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestCheckoutAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_without_buy_now_session_is_redirected_from_checkout(): void
    {
        $this->get(route('frontend.checkout'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_with_buy_now_session_can_open_checkout(): void
    {
        $this->withSession([
            'checkout' => [
                'source' => 'buy_now',
                'items' => [],
            ],
        ])->get(route('frontend.checkout'))
            ->assertOk();
    }

    public function test_buy_now_endpoint_does_not_require_authentication(): void
    {
        $this->postJson(route('frontend.checkout.buy-now'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_variant_id', 'quantity']);
    }

    public function test_guest_buy_now_session_can_reach_checkout_payment_endpoints(): void
    {
        $session = [
            'checkout' => [
                'source' => 'buy_now',
                'items' => [],
            ],
        ];

        $this->withSession($session)
            ->postJson(route('frontend.checkout.manual-payment'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items', 'company_id']);

        $this->withSession($session)
            ->postJson(route('frontend.checkout.midtrans.charge'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items', 'company_id']);

        $this->withSession($session)
            ->postJson(route('frontend.checkout.complete'))
            ->assertOk()
            ->assertJson(['ok' => true, 'cartCount' => 0]);
    }

    public function test_guest_without_buy_now_session_gets_json_unauthorized_on_payment_endpoint(): void
    {
        $this->postJson(route('frontend.checkout.manual-payment'), [])
            ->assertUnauthorized();
    }

    public function test_guest_cannot_open_an_order_that_is_not_owned_by_the_session(): void
    {
        $this->withSession([
            'checkout' => ['source' => 'buy_now', 'items' => []],
        ])->get(route('frontend.checkout.waiting', ['orderId' => 'MAN-NOT-OWNED']))
            ->assertRedirect(route('login'));
    }

    public function test_member_only_areas_still_require_authentication(): void
    {
        $this->get(route('frontend.cart'))->assertRedirect(route('login'));
        $this->get(route('frontend.checkout.orders'))->assertRedirect(route('login'));
        $this->get(route('frontend.profil'))->assertRedirect(route('login'));
        $this->post(route('frontend.profil.return-requests.store'))->assertRedirect(route('login'));
        $this->post(route('frontend.redeem.prepare-checkout'))->assertRedirect(route('login'));
        $this->get(route('frontend.wishlist.index'))->assertRedirect(route('login'));
        $this->get(route('frontend.notifications.index'))->assertRedirect(route('login'));
    }
}
