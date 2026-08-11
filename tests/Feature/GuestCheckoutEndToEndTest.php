<?php

namespace Tests\Feature;

use App\Mail\InvoiceOrder;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class GuestCheckoutEndToEndTest extends TestCase
{
    use RefreshDatabase;

    private function makeProductVariant(int $stock = 5): ProductVariant
    {
        $companyId = (int) Company::query()->value('id');
        $product = Product::create([
            'company_id' => $companyId,
            'name' => 'Produk E2E Guest',
            'slug' => 'produk-e2e-'.Str::lower(Str::random(8)),
            'status' => 'active',
        ]);
        $variant = Variant::create([
            'name' => 'Ukuran',
            'value' => Str::upper(Str::random(6)),
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'E2E-'.Str::upper(Str::random(6)),
            'price' => 95000,
            'stock' => $stock,
        ]);
    }

    private function checkoutPayload(ProductVariant $variant, array $overrides = []): array
    {
        return array_replace_recursive([
            'items' => [[
                'id' => $variant->product_id,
                'productVariantId' => $variant->id,
                'companyId' => $variant->product->company_id,
                'name' => $variant->product->name,
                'variant' => 'Ukuran '.$variant->variant->value,
                'price' => (int) $variant->price,
                'qty' => 1,
            ]],
            'company_id' => $variant->product->company_id,
            'shipping_cost' => 10000,
            'shipping_label' => 'JNE REG',
            'guest_name' => 'Guest End To End',
            'guest_email' => 'guest-e2e@example.test',
            'guest_phone' => '+628111111111',
            'shipping_address_line' => 'Jl. Pengujian No. 1',
            'shipping_city' => 'Jakarta',
            'shipping_district' => 'Setiabudi',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12910',
            'shipping_destination_id' => 99,
        ], $overrides);
    }

    public function test_guest_manual_checkout_runs_from_buy_now_through_proof_upload(): void
    {
        Mail::fake();
        Storage::fake('public');
        $variant = $this->makeProductVariant();

        $this->post(route('frontend.checkout.buy-now'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('frontend.checkout'));

        $this->get(route('frontend.checkout'))
            ->assertOk()
            ->assertSee('Checkout cepat tanpa akun');

        $checkout = $this->postJson(
            route('frontend.checkout.manual-payment'),
            $this->checkoutPayload($variant)
        )->assertOk()->assertJson(['ok' => true]);

        $transaction = Transaction::query()->where('order_id', $checkout->json('order_id'))->firstOrFail();
        $this->assertNull($transaction->user_id);
        $this->assertContains($transaction->order_id, session('guest_owned_orders'));
        Mail::assertSent(InvoiceOrder::class, fn (InvoiceOrder $mail) => $mail->hasTo('guest-e2e@example.test'));

        $this->post(route('manual-payment.proof', $transaction), [
            'payment_proof' => UploadedFile::fake()->image('bukti-e2e.jpg', 120, 120),
        ])->assertRedirect();

        $transaction->refresh();
        $this->assertSame('menunggu_verifikasi', $transaction->status);
        $this->assertNotNull($transaction->payment_proof_uploaded_at);
        $this->assertNotEmpty($transaction->payment_proof_path);
    }

    public function test_checkout_revalidates_stock_when_product_sells_out_after_buy_now(): void
    {
        $variant = $this->makeProductVariant(1);

        $this->post(route('frontend.checkout.buy-now'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('frontend.checkout'));

        $variant->update(['stock' => 0]);

        $this->postJson(
            route('frontend.checkout.manual-payment'),
            $this->checkoutPayload($variant)
        )->assertUnprocessable()->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_existing_member_manual_checkout_still_uses_member_identity(): void
    {
        Mail::fake();
        $variant = $this->makeProductVariant();
        $user = User::factory()->create(['email' => 'existing-member@example.test']);
        $payload = $this->checkoutPayload($variant);
        unset(
            $payload['guest_name'],
            $payload['guest_email'],
            $payload['guest_phone'],
            $payload['shipping_address_line'],
            $payload['shipping_city'],
            $payload['shipping_district'],
            $payload['shipping_province'],
            $payload['shipping_postal_code'],
            $payload['shipping_destination_id'],
        );

        $this->actingAs($user)
            ->withSession(['checkout' => ['source' => 'buy_now', 'items' => $payload['items']]])
            ->postJson(route('frontend.checkout.manual-payment'), $payload)
            ->assertOk();

        $transaction = Transaction::query()->firstOrFail();
        $this->assertSame($user->id, $transaction->user_id);
        $this->assertNull($transaction->manual_customer_email);
        Mail::assertSent(InvoiceOrder::class, fn (InvoiceOrder $mail) => $mail->hasTo($user->email));
    }

    public function test_existing_member_cart_address_and_redeem_checkout_still_work(): void
    {
        $variant = $this->makeProductVariant();
        $user = User::factory()->create(['point_balance' => 5000]);
        Address::create([
            'user_id' => $user->id,
            'label' => 'Kantor',
            'recipient_name' => 'Member Regression',
            'phone_country_code' => '+62',
            'phone_number' => '8123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12910',
            'address_line' => 'Jl. Member No. 10',
            'is_primary' => true,
        ]);

        $this->actingAs($user)
            ->postJson(route('frontend.cart.store'), [
                'product_variant_id' => $variant->id,
                'quantity' => 2,
            ])
            ->assertOk()
            ->assertJson(['cartCount' => 2]);

        $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();
        $this->postJson(route('frontend.cart.prepare-checkout'), [
            'cart_ids' => [$cart->id],
        ])->assertOk();

        $this->get(route('frontend.checkout'))
            ->assertOk()
            ->assertSee('Member Regression')
            ->assertSee('Jl. Member No. 10');

        $variant->product->update([
            'is_redeem_product' => true,
            'redeem_points' => 1000,
        ]);

        $this->post(route('frontend.redeem.prepare-checkout'), [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertRedirect(route('frontend.checkout'))
            ->assertSessionHas('checkout.source', 'redeem_point');
    }

    public function test_existing_member_midtrans_checkout_still_creates_member_order(): void
    {
        Mail::fake();
        Http::fake(['*' => Http::response([
            'transaction_id' => 'midtrans-member-regression',
            'transaction_status' => 'pending',
            'payment_type' => 'qris',
            'expiry_time' => now()->addMinutes(30)->toDateTimeString(),
            'actions' => [[
                'name' => 'generate-qr-code',
                'url' => 'https://example.test/member-qr.png',
            ]],
        ])]);
        $oldServerKey = getenv('MIDTRANS_SERVER_KEY');
        putenv('MIDTRANS_SERVER_KEY=test-server-key');

        $variant = $this->makeProductVariant();
        $user = User::factory()->create(['email' => 'member-midtrans@example.test']);
        $payload = $this->checkoutPayload($variant, ['payment_method' => 'qris']);
        unset(
            $payload['guest_name'],
            $payload['guest_email'],
            $payload['guest_phone'],
            $payload['shipping_address_line'],
            $payload['shipping_city'],
            $payload['shipping_district'],
            $payload['shipping_province'],
            $payload['shipping_postal_code'],
            $payload['shipping_destination_id'],
        );

        try {
            $this->actingAs($user)
                ->withSession(['checkout' => ['source' => 'buy_now', 'items' => $payload['items']]])
                ->postJson(route('frontend.checkout.midtrans.charge'), $payload)
                ->assertOk()
                ->assertJsonStructure(['order_id', 'redirect_url']);
        } finally {
            $oldServerKey === false
                ? putenv('MIDTRANS_SERVER_KEY')
                : putenv('MIDTRANS_SERVER_KEY='.$oldServerKey);
        }

        $transaction = Transaction::query()->firstOrFail();
        $this->assertSame($user->id, $transaction->user_id);
        $this->assertNull($transaction->manual_customer_email);
        Mail::assertSent(InvoiceOrder::class, fn (InvoiceOrder $mail) => $mail->hasTo($user->email));
    }

    public function test_expired_guest_midtrans_order_is_cancelled_when_waiting_page_is_opened(): void
    {
        Http::fake(['*' => Http::response(['status_code' => '200'])]);
        $oldServerKey = getenv('MIDTRANS_SERVER_KEY');
        putenv('MIDTRANS_SERVER_KEY=test-server-key');

        $transaction = Transaction::create([
            'company_id' => Company::query()->value('id'),
            'user_id' => null,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-EXPIRED-GUEST',
            'order_id' => 'ORD-EXPIRED-GUEST',
            'payment_type' => 'qris',
            'payment_method' => 'QRIS',
            'status' => 'pending',
            'subtotal_amount' => 50000,
            'shipping_cost' => 0,
            'grand_total' => 50000,
            'manual_customer_name' => 'Guest Expired',
            'manual_customer_email' => 'expired@example.test',
            'expires_at' => now()->subMinute(),
        ]);
        $transaction->details()->create([
            'product_name' => 'Produk Kedaluwarsa',
            'price' => 50000,
            'quantity' => 1,
            'subtotal' => 50000,
        ]);

        try {
            $this->withSession([
                'checkout' => ['source' => 'buy_now', 'items' => []],
                'guest_owned_orders' => [$transaction->order_id],
            ])->get(route('frontend.checkout.waiting', $transaction->order_id))
                ->assertOk()
                ->assertSee('DIBATALKAN');
        } finally {
            $oldServerKey === false
                ? putenv('MIDTRANS_SERVER_KEY')
                : putenv('MIDTRANS_SERVER_KEY='.$oldServerKey);
        }

        $transaction->refresh();
        $this->assertSame('dibatalkan', $transaction->status);
        $this->assertNotNull($transaction->cancelled_at);
        $this->assertStringContainsString('kadaluarsa', (string) $transaction->cancel_reason);
    }
}
