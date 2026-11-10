<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use App\Services\CheckoutPricingService;
use App\Services\ShippingQuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class MobileCheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile-spa.enabled' => true]);
        $this->seed();
    }

    public function test_checkout_preview_is_member_only_and_groups_server_priced_cart(): void
    {
        $this->getJson('/api/mobile/v1/checkout')->assertUnauthorized();

        [$user, $address, $cart] = $this->checkoutFixture();
        $this->actingAs($user)->getJson('/api/mobile/v1/checkout')
            ->assertOk()
            ->assertJsonPath('data.addresses.0.id', $address->id)
            ->assertJsonPath('data.groups.0.cart_ids.0', $cart->id)
            ->assertJsonPath('data.groups.0.items.0.productVariantId', $cart->product_variant_id)
            ->assertJsonPath('data.groups.0.subtotal', (int) $cart->productVariant->price * $cart->quantity)
            ->assertJsonMissingPath('data.groups.0.items.0.stock');
    }

    public function test_manual_order_is_idempotent_and_uses_owned_address_and_server_totals(): void
    {
        Mail::fake();
        [$user, $address, $cart] = $this->checkoutFixture();
        $this->actingAs($user);
        $preview = $this->getJson('/api/mobile/v1/checkout')->assertOk();
        $group = $preview->json('data.groups.0');
        $quote = $this->quote($group, $address, 15_000, 'JNE REG');
        $key = (string) Str::uuid();
        $payload = [
            'company_id' => $group['company_id'],
            'cart_ids' => $group['cart_ids'],
            'address_id' => $address->id,
            'shipping_quote_token' => $quote,
            'checkout_version' => $group['checkout_version'],
            'payment_method' => 'manual',
        ];

        $created = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/mobile/v1/checkout/orders', $payload)
            ->assertOk()
            ->assertJsonPath('ok', true);

        $orderId = $created->json('order_id');
        $transaction = Transaction::query()->where('order_id', $orderId)->firstOrFail();
        $this->assertSame($key, $transaction->idempotency_key);
        $this->assertSame((int) $group['total_before_shipping'] + 15_000, (int) $transaction->grand_total);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);

        $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/mobile/v1/checkout/orders', $payload)
            ->assertOk()
            ->assertJsonPath('order_id', $orderId);
        $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/mobile/v1/checkout/orders', array_merge($payload, ['payment_method' => 'qris']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idempotency_key');
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
    }

    public function test_checkout_rejects_cross_customer_address_stale_version_and_reused_key_payload(): void
    {
        [$user, $address, $cart] = $this->checkoutFixture();
        $otherAddress = $this->makeAddress(User::factory()->create());
        $this->actingAs($user);
        $group = $this->getJson('/api/mobile/v1/checkout')->json('data.groups.0');
        $quote = $this->quote($group, $address, 0, 'Gratis Ongkir');
        $base = [
            'company_id' => $group['company_id'],
            'cart_ids' => $group['cart_ids'],
            'shipping_quote_token' => $quote,
            'checkout_version' => $group['checkout_version'],
            'payment_method' => 'manual',
        ];

        $this->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/mobile/v1/checkout/orders', $base + ['address_id' => $otherAddress->id])
            ->assertNotFound();

        $cart->productVariant()->update(['price' => (int) $cart->productVariant->price + 1000]);
        $this->withHeader('Idempotency-Key', (string) Str::uuid())
            ->postJson('/api/mobile/v1/checkout/orders', $base + ['address_id' => $address->id])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('checkout_version');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_payment_read_is_scoped_to_order_owner(): void
    {
        [$user] = $this->checkoutFixture();
        $transaction = Transaction::query()->create([
            'company_id' => Company::query()->value('id'),
            'user_id' => $user->id,
            'invoice_no' => 'INV-MOBILE-OWNER',
            'order_id' => 'ORD-MOBILE-OWNER',
            'status' => 'pending',
            'subtotal_amount' => 10_000,
            'shipping_cost' => 0,
            'grand_total' => 10_000,
        ]);

        $this->actingAs(User::factory()->create())
            ->getJson('/api/mobile/v1/orders/'.$transaction->order_id.'/payment')
            ->assertNotFound();
    }

    public function test_midtrans_order_replay_does_not_duplicate_provider_charge_or_reservation(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response([
            'transaction_id' => 'mobile-midtrans-1',
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
            'expiry_time' => now()->addMinutes(30)->toDateTimeString(),
            'actions' => [['name' => 'generate-qr-code', 'url' => 'https://example.test/qr.png']],
        ])]);
        config(['services.midtrans.server_key' => 'mobile-test-key', 'services.midtrans.is_production' => false]);
        [$user, $address] = $this->checkoutFixture();
        $this->actingAs($user);
        $group = $this->getJson('/api/mobile/v1/checkout')->json('data.groups.0');
        $key = (string) Str::uuid();
        $payload = [
            'company_id' => $group['company_id'],
            'cart_ids' => $group['cart_ids'],
            'address_id' => $address->id,
            'shipping_quote_token' => $this->quote($group, $address, 0, 'Gratis Ongkir'),
            'checkout_version' => $group['checkout_version'],
            'payment_method' => 'qris',
        ];

        $first = $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/mobile/v1/checkout/orders', $payload)
            ->assertOk();
        $this->withHeader('Idempotency-Key', $key)
            ->postJson('/api/mobile/v1/checkout/orders', $payload)
            ->assertOk()
            ->assertJsonPath('order_id', $first->json('order_id'));

        Http::assertSentCount(1);
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseCount('inventory_reservations', 1);
        $this->getJson('/api/mobile/v1/orders/'.$first->json('order_id').'/payment')
            ->assertOk()
            ->assertJsonPath('data.qr_url', 'https://example.test/qr.png');
    }

    public function test_multi_company_checkout_creates_independent_orders_and_clears_each_cart_group(): void
    {
        Mail::fake();
        [$user, $address] = $this->checkoutFixture();
        $company = Company::query()->create(['name' => 'Toko Kedua', 'slug' => 'toko-kedua', 'invoice_prefix' => 'TK2', 'is_active' => true]);
        $product = Product::query()->create(['company_id' => $company->id, 'name' => 'Produk Toko Kedua', 'slug' => 'produk-toko-kedua', 'status' => 'active']);
        $variantName = Variant::query()->create(['name' => 'Ukuran', 'value' => 'Kedua']);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variantName->id,
            'sku' => 'TOKO-KEDUA',
            'price' => 50_000,
            'stock' => 5,
            'weight_grams' => 500,
        ]);
        Cart::query()->create(['user_id' => $user->id, 'product_variant_id' => $variant->id, 'quantity' => 1]);
        $this->actingAs($user);
        $groups = $this->getJson('/api/mobile/v1/checkout')->assertOk()->json('data.groups');
        $this->assertCount(2, $groups);

        foreach ($groups as $group) {
            $this->withHeader('Idempotency-Key', (string) Str::uuid())
                ->postJson('/api/mobile/v1/checkout/orders', [
                    'company_id' => $group['company_id'],
                    'cart_ids' => $group['cart_ids'],
                    'address_id' => $address->id,
                    'shipping_quote_token' => $this->quote($group, $address, 0, 'Gratis Ongkir'),
                    'checkout_version' => $group['checkout_version'],
                    'payment_method' => 'manual',
                ])
                ->assertOk();
        }

        $this->assertDatabaseCount('transactions', 2);
        $this->assertDatabaseCount('carts', 0);
        $this->assertSame(2, Transaction::query()->distinct()->count('company_id'));
    }

    private function checkoutFixture(): array
    {
        $user = User::factory()->create();
        $address = $this->makeAddress($user);
        $variant = ProductVariant::query()
            ->where('stock', '>=', 2)
            ->whereHas('product', fn ($query) => $query->storefrontVisible())
            ->firstOrFail();
        $cart = Cart::query()->create([
            'user_id' => $user->id,
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $cart->setRelation('productVariant', $variant);

        return [$user, $address, $cart];
    }

    private function makeAddress(User $user): Address
    {
        return Address::query()->create([
            'user_id' => $user->id,
            'label' => 'Rumah',
            'recipient_name' => 'Penerima Mobile',
            'phone_country_code' => '+62',
            'phone_number' => '8123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Setiabudi',
            'postal_code' => '12910',
            'destination_id' => 99,
            'address_line' => 'Jl. Mobile No. 1',
            'is_primary' => true,
        ]);
    }

    private function quote(array $group, Address $address, int $cost, string $label): string
    {
        $pricing = app(CheckoutPricingService::class)->resolve($group['request_items'], (int) $group['company_id']);

        return app(ShippingQuoteService::class)->issue(
            (int) $group['company_id'],
            (int) $address->destination_id,
            $pricing['fingerprint'],
            $pricing['weight_grams'],
            $cost,
            $label,
        );
    }
}
