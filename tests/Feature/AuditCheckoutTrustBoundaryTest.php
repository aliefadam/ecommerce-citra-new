<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\FlashSale;
use App\Models\FlashSaleReservation;
use App\Models\InventoryReservation;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesCheckoutShippingQuotes;
use Tests\TestCase;

class AuditCheckoutTrustBoundaryTest extends TestCase
{
    use CreatesCheckoutShippingQuotes;
    use RefreshDatabase;

    public function test_manual_checkout_never_trusts_client_product_snapshot(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(price: 125_000);

        $response = $this->withSession($this->guestSession())->postJson(
            route('frontend.checkout.manual-payment'),
            $this->guestPayload($company, $product, $productVariant, [
                'items' => [[
                    'id' => $product->id,
                    'productVariantId' => $productVariant->id,
                    'companyId' => $company->id,
                    'name' => 'Nama produk hasil manipulasi',
                    'image' => 'https://attacker.invalid/fake.png',
                    'price' => 1,
                    'qty' => 1,
                ]],
            ])
        );

        // Implementasi aman boleh menolak snapshot client yang dimanipulasi, atau
        // menerima request tetapi selalu membangun ulang snapshot dari database.
        if ($response->status() === 422) {
            $this->assertDatabaseCount('transactions', 0);

            return;
        }

        $response->assertOk();
        $transaction = Transaction::query()->with('details')->sole();
        $detail = $transaction->details->sole();

        $this->assertSame(125_000, (int) $transaction->subtotal_amount);
        $this->assertSame(125_000, (int) $detail->price);
        $this->assertSame('Produk Audit Checkout', $detail->product_name);
        $this->assertNotSame('https://attacker.invalid/fake.png', (string) $detail->image);
    }

    public function test_manual_checkout_rejects_arbitrary_shipping_cost_without_server_quote(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct();

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant,
                ['shipping_cost' => 1, 'shipping_label' => 'ONGKIR PALSU', 'shipping_quote_token' => null]
            ))
            ->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_manual_checkout_ignores_shipping_values_that_conflict_with_valid_quote(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct();
        $payload = $this->guestPayload($company, $product, $productVariant);
        $payload['shipping_cost'] = 1;
        $payload['shipping_label'] = 'ONGKIR PALSU';

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $payload)
            ->assertOk();

        $transaction = Transaction::query()->sole();
        $this->assertSame(0, (int) $transaction->shipping_cost);
        $this->assertSame('Gratis Ongkir Audit', $transaction->shipping_label);
    }

    public function test_midtrans_checkout_uses_the_same_server_product_snapshot(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response([
            'transaction_id' => 'audit-midtrans-1',
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
            'expiry_time' => now()->addMinutes(30)->toDateTimeString(),
            'actions' => [],
        ])]);
        Config::set('services.midtrans.server_key', 'audit-server-key');
        Config::set('services.midtrans.is_production', false);

        [$company, $product, $productVariant] = $this->makeProduct(price: 125_000);
        $payload = $this->guestPayload($company, $product, $productVariant, [
            'payment_method' => 'qris',
            'items' => [[
                'name' => 'Produk Palsu Midtrans',
                'price' => 1,
            ]],
        ]);

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.midtrans.charge'), $payload)
            ->assertOk();

        $transaction = Transaction::query()->with('details')->sole();
        $this->assertSame(125_000, (int) $transaction->subtotal_amount);
        $this->assertSame(125_000, (int) $transaction->details->sole()->price);
        $this->assertSame('Produk Audit Checkout', $transaction->details->sole()->product_name);

        Http::assertSent(fn ($request) => (int) $request['item_details'][0]['price'] === 125_000
            && $request['item_details'][0]['name'] === 'Produk Audit Checkout');
    }

    public function test_second_checkout_cannot_claim_stock_already_reserved_by_first_order(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(stock: 1);

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant
            ))
            ->assertOk();

        $this->flushSession();

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant,
                ['guest_email' => 'guest-kedua@example.test']
            ))
            ->assertUnprocessable();

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_exhausted_flash_sale_quota_uses_regular_price(): void
    {
        [$company, $product, $productVariant] = $this->makeProduct(price: 125_000);
        $flashSale = FlashSale::query()->create([
            'company_id' => $company->id,
            'name' => 'Flash Sale Audit',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
            'status' => 'active',
        ]);
        $flashSale->items()->create([
            'product_variant_id' => $productVariant->id,
            'discount_price' => 10_000,
            'quota' => 1,
            'sold' => 1,
            'is_active' => true,
        ]);

        $this->post(route('frontend.checkout.buy-now'), [
            'product_variant_id' => $productVariant->id,
            'quantity' => 1,
        ])->assertRedirect(route('frontend.checkout'));

        $item = session('checkout.items.0');
        $this->assertSame(125_000, (int) ($item['price'] ?? 0));
        $this->assertFalse((bool) ($item['isFlashSale'] ?? true));
    }

    public function test_reserved_stock_and_coupon_are_committed_exactly_once_through_fulfillment(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(price: 100_000, stock: 1);
        $coupon = Coupon::query()->create([
            'company_id' => $company->id,
            'code' => 'LIMIT1',
            'name' => 'Voucher Terbatas',
            'type' => 'fixed',
            'value' => 10_000,
            'usage_limit' => 1,
            'is_active' => true,
        ]);

        $response = $this->withSession(array_merge($this->guestSession(), [
            'checkout_coupon' => [$company->id => ['code' => 'LIMIT1', 'discount_amount' => 10_000]],
        ]))->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload($company, $product, $productVariant));

        $response->assertOk();
        $transaction = Transaction::query()->sole();
        $this->assertSame(1, (int) $productVariant->fresh()->stock);
        $this->assertDatabaseHas('inventory_reservations', ['transaction_id' => $transaction->id, 'status' => 'reserved']);
        $this->assertDatabaseHas('coupon_redemptions', ['transaction_id' => $transaction->id, 'status' => 'reserved']);
        $this->assertSame(0, (int) $coupon->fresh()->used_count);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $company->id])
            ->patch(route('transactions.verify-payment', $transaction), ['action' => 'approve'])
            ->assertRedirect();

        $this->assertSame(1, (int) $coupon->fresh()->used_count);
        $this->assertSame('redeemed', CouponRedemption::query()->where('transaction_id', $transaction->id)->value('status'));

        $this->patchJson(route('transactions.process', $transaction))->assertOk();
        $this->patchJson(route('transactions.process', $transaction))->assertUnprocessable();
        $this->assertSame(0, (int) $productVariant->fresh()->stock);
        $this->assertSame('committed', InventoryReservation::query()->where('transaction_id', $transaction->id)->value('status'));
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_cancelling_pending_order_releases_stock_for_the_next_checkout(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(stock: 1);

        $created = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload($company, $product, $productVariant))
            ->assertOk();

        $orderId = (string) $created->json('order_id');
        $this->postJson(route('frontend.checkout.midtrans.cancel', $orderId), ['cancel_reason' => 'Uji release'])
            ->assertOk();
        $this->assertSame('released', InventoryReservation::query()->sole()->status);

        $this->flushSession();
        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant,
                ['guest_email' => 'guest-pengganti@example.test']
            ))
            ->assertOk();

        $this->assertDatabaseCount('transactions', 2);
    }

    public function test_flash_sale_quota_is_reserved_released_and_committed_without_double_counting(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(price: 125_000, stock: 3);
        $flashSale = FlashSale::query()->create([
            'company_id' => $company->id,
            'name' => 'Flash Sale Atomic',
            'start_at' => now()->subMinute(),
            'end_at' => now()->addHour(),
            'status' => 'active',
        ]);
        $flashItem = $flashSale->items()->create([
            'product_variant_id' => $productVariant->id,
            'discount_price' => 10_000,
            'quota' => 1,
            'sold' => 0,
            'is_active' => true,
        ]);

        $first = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload($company, $product, $productVariant))
            ->assertOk();
        $firstTransaction = Transaction::query()->where('order_id', $first->json('order_id'))->firstOrFail();
        $this->assertSame(10_000, (int) $firstTransaction->subtotal_amount);
        $this->assertSame('reserved', FlashSaleReservation::query()->sole()->status);
        $this->assertSame(0, (int) $flashItem->fresh()->sold);

        $this->flushSession();
        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant,
                ['guest_email' => 'flash-kedua@example.test']
            ))
            ->assertUnprocessable();

        app(\App\Services\CommerceReservationService::class)->release($firstTransaction);
        $this->flushSession();
        $replacement = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload(
                $company,
                $product,
                $productVariant,
                ['guest_email' => 'flash-pengganti@example.test']
            ))
            ->assertOk();

        $replacementTransaction = Transaction::query()->where('order_id', $replacement->json('order_id'))->firstOrFail();
        app(\App\Services\CommerceReservationService::class)->commitInventory($replacementTransaction);
        app(\App\Services\CommerceReservationService::class)->commitInventory($replacementTransaction);

        $this->assertSame(1, (int) $flashItem->fresh()->sold);
        $this->assertSame('committed', FlashSaleReservation::query()->where('transaction_id', $replacementTransaction->id)->value('status'));
    }

    public function test_released_stock_reservation_cannot_be_approved_as_paid(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(stock: 1);
        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload($company, $product, $productVariant))
            ->assertOk();

        $transaction = Transaction::query()->sole();
        app(\App\Services\CommerceReservationService::class)->release($transaction);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $company->id])
            ->patch(route('transactions.verify-payment', $transaction), ['action' => 'approve'])
            ->assertSessionHasErrors('stock');

        $this->assertNotSame('paid', $transaction->fresh()->status);
        $this->assertSame('released', InventoryReservation::query()->sole()->status);
    }

    public function test_expired_reservations_are_released_idempotently(): void
    {
        Mail::fake();
        [$company, $product, $productVariant] = $this->makeProduct(stock: 1);
        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload($company, $product, $productVariant))
            ->assertOk();

        $transaction = Transaction::query()->sole();
        $transaction->update(['expires_at' => now()->subMinute()]);

        $this->artisan('commerce:release-expired-reservations')->assertSuccessful();
        $this->artisan('commerce:release-expired-reservations')->assertSuccessful();

        $this->assertSame('dibatalkan', $transaction->fresh()->status);
        $this->assertSame('released', InventoryReservation::query()->sole()->status);
        $this->assertSame(1, $transaction->statusHistories()->where('type', 'reservation_expired')->count());
    }

    private function makeProduct(int $price = 100_000, int $stock = 5): array
    {
        $company = Company::query()->where('slug', 'boq')->firstOrFail();
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Produk Audit Checkout',
            'slug' => 'produk-audit-checkout',
            'status' => 'active',
        ]);
        $variant = Variant::query()->create([
            'name' => 'Ukuran Audit',
            'value' => 'Standar',
        ]);
        $productVariant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'AUDIT-CHECKOUT-001',
            'price' => $price,
            'stock' => $stock,
            'weight_grams' => 1_000,
        ]);

        return [$company, $product, $productVariant];
    }

    private function guestSession(): array
    {
        return [
            'checkout' => [
                'source' => 'buy_now',
                'items' => [],
            ],
        ];
    }

    private function guestPayload(
        Company $company,
        Product $product,
        ProductVariant $productVariant,
        array $overrides = []
    ): array {
        $payload = array_replace_recursive([
            'items' => [[
                'id' => $product->id,
                'productVariantId' => $productVariant->id,
                'companyId' => $company->id,
                'name' => $product->name,
                'variant' => 'Standar',
                'price' => (int) $productVariant->price,
                'qty' => 1,
            ]],
            'company_id' => $company->id,
            'shipping_cost' => 0,
            'shipping_label' => 'Gratis Ongkir Audit',
            'guest_name' => 'Guest Audit',
            'guest_email' => 'guest-audit@example.test',
            'guest_phone' => '+628123456789',
            'shipping_address_line' => 'Jl. Audit No. 1',
            'shipping_city' => 'Jakarta',
            'shipping_district' => 'Setiabudi',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12910',
            'shipping_destination_id' => 123,
        ], $overrides);

        if (! array_key_exists('shipping_quote_token', $payload)) {
            $payload['shipping_quote_token'] = $this->checkoutShippingQuote(
                $payload['items'],
                (int) $payload['company_id'],
                (int) $payload['shipping_destination_id'],
                (int) $payload['shipping_cost'],
                (string) $payload['shipping_label'],
            );
        }

        return $payload;
    }
}
