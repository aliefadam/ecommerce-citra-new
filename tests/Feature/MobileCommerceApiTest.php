<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileCommerceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['mobile-spa.enabled' => true]);
        $this->seed();
    }

    public function test_cart_and_wishlist_require_an_authenticated_session(): void
    {
        $this->getJson('/api/mobile/v1/cart')->assertUnauthorized()->assertJsonPath('code', 'unauthenticated');
        $this->getJson('/api/mobile/v1/wishlist')->assertUnauthorized();
    }

    public function test_member_can_add_update_and_remove_cart_item_with_server_authoritative_values(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::query()
            ->where('stock', '>=', 3)
            ->whereHas('product', fn ($query) => $query->storefrontVisible())
            ->firstOrFail();

        $response = $this->actingAs($user)->postJson('/api/mobile/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
            'unit_price' => 1,
        ])
            ->assertCreated()
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.unit_price', (int) $variant->price)
            ->assertJsonPath('data.summary.quantity', 2)
            ->assertJsonMissingPath('data.items.0.product.company_id');

        $cartId = (int) $response->json('data.items.0.id');

        $this->patchJson('/api/mobile/v1/cart/items/'.$cartId, ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.items.0.quantity', 3);

        $this->deleteJson('/api/mobile/v1/cart/items/'.$cartId)->assertNoContent();
        $this->assertDatabaseMissing('carts', ['id' => $cartId]);
    }

    public function test_cart_rejects_stock_tampering_hidden_products_and_cross_customer_ids(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $variant = ProductVariant::query()
            ->where('stock', '>', 0)
            ->whereHas('product', fn ($query) => $query->storefrontVisible())
            ->firstOrFail();

        $this->actingAs($owner)->postJson('/api/mobile/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => (int) $variant->stock + 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('quantity');

        $cart = Cart::query()->create([
            'user_id' => $owner->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($attacker)
            ->patchJson('/api/mobile/v1/cart/items/'.$cart->id, ['quantity' => 1])
            ->assertNotFound();
        $this->deleteJson('/api/mobile/v1/cart/items/'.$cart->id)->assertNotFound();

        $variant->product()->update(['status' => 'inactive']);
        $this->actingAs($owner)->postJson('/api/mobile/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('product_variant_id');
    }

    public function test_cart_read_reconciles_changed_stock_without_trusting_stale_client_state(): void
    {
        $user = User::factory()->create();
        $variant = ProductVariant::query()
            ->where('stock', '>=', 2)
            ->whereHas('product', fn ($query) => $query->storefrontVisible())
            ->firstOrFail();
        Cart::query()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id, 'quantity' => 2]);
        $variant->update(['stock' => 1]);

        $this->actingAs($user)->getJson('/api/mobile/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.items.0.available', false)
            ->assertJsonPath('data.items.0.max_quantity', 1)
            ->assertJsonPath('data.summary.has_issues', true)
            ->assertJsonPath('data.summary.subtotal', 0);
    }

    public function test_wishlist_put_and_delete_are_idempotent_and_hide_unavailable_products(): void
    {
        $user = User::factory()->create();
        $product = Product::query()->storefrontVisible()->whereHas('productVariants')->firstOrFail();

        $this->actingAs($user)->putJson('/api/mobile/v1/wishlist/'.$product->slug)->assertCreated();
        $this->putJson('/api/mobile/v1/wishlist/'.$product->slug)->assertCreated();
        $this->assertSame(1, Wishlist::query()->where('user_id', $user->id)->where('product_id', $product->id)->count());

        $this->getJson('/api/mobile/v1/wishlist')
            ->assertOk()
            ->assertJsonPath('meta.count', 1)
            ->assertJsonPath('data.0.slug', $product->slug);

        $product->update(['status' => 'inactive']);
        $this->getJson('/api/mobile/v1/wishlist')->assertJsonPath('meta.count', 0);

        $this->deleteJson('/api/mobile/v1/wishlist/'.$product->slug)->assertNoContent();
        $this->deleteJson('/api/mobile/v1/wishlist/'.$product->slug)->assertNoContent();
    }

    public function test_mobile_registration_normalizes_email_and_rejects_case_insensitive_duplicate(): void
    {
        $this->withHeader('Origin', 'http://localhost')->postJson('/api/mobile/v1/auth/register', [
            'name' => 'Mobile Customer',
            'email' => 'NEW.Customer@Example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'return_url' => '/app/cart',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.email', 'new.customer@example.test')
            ->assertJsonPath('data.return_url', '/app/cart');

        $this->assertAuthenticated();
        $this->app['auth']->forgetGuards();

        $this->withHeader('Origin', 'http://localhost')->postJson('/api/mobile/v1/auth/register', [
            'name' => 'Duplicate',
            'email' => 'new.CUSTOMER@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }
}
