<?php

namespace Tests\Feature;

use App\Mail\InvoiceOrder;
use App\Models\Company;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesCheckoutShippingQuotes;
use Tests\TestCase;

class GuestCheckoutDataTest extends TestCase
{
    use CreatesCheckoutShippingQuotes;
    use RefreshDatabase;

    private function guestSession(): array
    {
        return [
            'checkout' => [
                'source' => 'buy_now',
                'items' => [],
            ],
        ];
    }

    private function guestPayload(array $overrides = []): array
    {
        $companyId = (int) Company::query()->value('id');
        $suffix = Str::upper(Str::random(8));
        $product = Product::query()->create([
            'company_id' => $companyId,
            'name' => 'Produk Guest',
            'slug' => 'produk-guest-'.Str::lower($suffix),
            'status' => 'active',
        ]);
        $variant = Variant::query()->create(['name' => 'Ukuran', 'value' => $suffix]);
        $productVariant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'GUEST-'.$suffix,
            'price' => 125000,
            'stock' => 10,
            'weight_grams' => 1000,
        ]);

        $payload = array_merge([
            'items' => [[
                'id' => $product->id,
                'productVariantId' => $productVariant->id,
                'companyId' => $companyId,
                'name' => 'Produk Guest',
                'price' => 125000,
                'qty' => 1,
            ]],
            'company_id' => $companyId,
            'shipping_cost' => 15000,
            'shipping_label' => 'JNE REG',
            'guest_name' => 'Pembeli Guest',
            'guest_email' => 'guest@example.test',
            'guest_phone' => '+628123456789',
            'shipping_address_line' => 'Jl. Tanpa Login No. 10',
            'shipping_city' => 'Bandung',
            'shipping_district' => 'Coblong',
            'shipping_province' => 'Jawa Barat',
            'shipping_postal_code' => '40132',
            'shipping_destination_id' => 123,
        ], $overrides);

        $payload['shipping_quote_token'] = $this->checkoutShippingQuote(
            $payload['items'],
            (int) $payload['company_id'],
            (int) $payload['shipping_destination_id'],
            (int) $payload['shipping_cost'],
            (string) $payload['shipping_label'],
        );

        return $payload;
    }

    public function test_guest_checkout_page_renders_manual_shipping_form(): void
    {
        $this->withSession($this->guestSession())
            ->get(route('frontend.checkout'))
            ->assertOk()
            ->assertSee('Checkout cepat tanpa akun', false)
            ->assertSee('Data Pengiriman', false)
            ->assertSee('id="guestEmail"', false);
    }

    public function test_guest_payment_requires_contact_and_shipping_fields(): void
    {
        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), [
                'items' => [['id' => 1, 'name' => 'Produk', 'price' => 10000, 'qty' => 1]],
                'company_id' => Company::query()->value('id'),
                'shipping_cost' => 0,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'guest_name',
                'guest_email',
                'guest_phone',
                'shipping_address_line',
                'shipping_city',
                'shipping_province',
                'shipping_postal_code',
                'shipping_destination_id',
            ]);
    }

    public function test_registered_email_is_redirected_to_login_without_losing_checkout_session(): void
    {
        User::factory()->create(['email' => 'member@example.test']);

        $response = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload([
                'guest_email' => 'MEMBER@example.test',
            ]));

        $response->assertStatus(409)->assertJson([
            'requires_login' => true,
        ]);
        $this->assertStringContainsString('redirect=', $response->json('login_url'));
        $this->assertSame('buy_now', session('checkout.source'));
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_guest_manual_checkout_saves_contact_and_shipping_snapshot(): void
    {
        Mail::fake();

        $response = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $this->guestPayload());

        $response->assertOk()->assertJson(['ok' => true]);
        $transaction = Transaction::query()->firstOrFail();

        $this->assertMatchesRegularExpression('/^MAN-\d{14}-[A-Z0-9]{10}$/', $transaction->order_id);
        $this->assertNull($transaction->user_id);
        $this->assertSame('Pembeli Guest', $transaction->manual_customer_name);
        $this->assertSame('guest@example.test', $transaction->manual_customer_email);
        $this->assertSame('+628123456789', $transaction->manual_customer_phone);
        $this->assertSame('Jl. Tanpa Login No. 10', $transaction->shipping_address_line);
        $this->assertSame('Coblong', $transaction->shipping_district);
        $this->assertSame('buy_now', session('checkout.source'));
        $this->assertContains($transaction->order_id, session('guest_owned_orders'));
        Mail::assertSent(InvoiceOrder::class, fn (InvoiceOrder $mail) => $mail->hasTo('guest@example.test'));
    }

    public function test_member_only_coupon_is_rejected_for_guest_and_allowed_for_member(): void
    {
        $coupon = Coupon::create([
            'company_id' => Company::query()->value('id'),
            'code' => 'MEMBER10',
            'name' => 'Diskon Member',
            'type' => 'fixed',
            'value' => 10000,
            'min_purchase' => 0,
            'is_active' => true,
            'is_member_only' => true,
        ]);

        $payload = ['code' => $coupon->code, 'subtotal' => 125000, 'company_id' => $coupon->company_id];

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.coupon.apply'), $payload)
            ->assertUnprocessable();

        $this->actingAs(User::factory()->create())
            ->postJson(route('frontend.checkout.coupon.apply'), $payload)
            ->assertOk()
            ->assertJson(['code' => 'MEMBER10', 'discount_amount' => 10000]);
    }

    public function test_guest_midtrans_checkout_sends_guest_details_and_saves_snapshot(): void
    {
        Mail::fake();
        Http::fake([
            '*' => Http::response([
                'transaction_id' => 'midtrans-guest-1',
                'transaction_status' => 'pending',
                'payment_type' => 'qris',
                'expiry_time' => now()->addMinutes(30)->toDateTimeString(),
                'actions' => [[
                    'name' => 'generate-qr-code',
                    'url' => 'https://example.test/qr.png',
                ]],
            ]),
        ]);

        Config::set('services.midtrans.server_key', 'test-server-key');
        Config::set('services.midtrans.is_production', false);

        $response = $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.midtrans.charge'), $this->guestPayload([
                'payment_method' => 'qris',
            ]));

        $response->assertOk()->assertJsonStructure(['order_id', 'redirect_url']);
        $transaction = Transaction::query()->firstOrFail();
        $this->assertMatchesRegularExpression('/^ORD-\d{14}-[A-Z0-9]{10}$/', $transaction->order_id);
        $this->assertNull($transaction->user_id);
        $this->assertSame('guest@example.test', $transaction->manual_customer_email);
        $this->assertSame('Bandung', $transaction->shipping_city);
        Mail::assertSent(InvoiceOrder::class, fn (InvoiceOrder $mail) => $mail->hasTo('guest@example.test'));

        Http::assertSent(fn ($request) => $request['customer_details']['email'] === 'guest@example.test'
            && $request['customer_details']['first_name'] === 'Pembeli Guest'
        );
    }

    public function test_guest_cannot_use_member_redeem_points_from_ui_or_request_payload(): void
    {
        $this->withSession($this->guestSession())
            ->get(route('frontend.checkout'))
            ->assertOk()
            ->assertDontSee('Produk tetap ditukar dengan point');

        $payload = $this->guestPayload();
        $payload['items'][0]['redeemPoints'] = 500;

        $this->withSession($this->guestSession())
            ->postJson(route('frontend.checkout.manual-payment'), $payload)
            ->assertUnprocessable()
            ->assertJson([
                'message' => 'Redeem poin hanya tersedia untuk member yang sudah login.',
            ]);

        $this->assertDatabaseCount('transactions', 0);
    }
}
