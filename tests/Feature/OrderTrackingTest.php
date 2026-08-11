<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeTransaction(array $overrides = []): Transaction
    {
        $transaction = Transaction::create(array_merge([
            'company_id' => Company::query()->value('id'),
            'user_id' => null,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-TRACK-'.uniqid(),
            'order_id' => 'MAN-TRACK-'.strtoupper(uniqid()),
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
            'status' => 'pending',
            'subtotal_amount' => 100000,
            'shipping_cost' => 10000,
            'discount_amount' => 0,
            'grand_total' => 110000,
            'shipping_recipient_name' => 'Tamu Pelacak',
            'manual_customer_name' => 'Tamu Pelacak',
            'manual_customer_email' => 'track@example.test',
        ], $overrides));

        $transaction->details()->create([
            'product_name' => 'Produk Rahasia Pelacakan',
            'variant_name' => 'Biru',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        return $transaction;
    }

    public function test_tracking_page_is_public_but_does_not_expose_order_before_verification(): void
    {
        $transaction = $this->makeTransaction();

        $this->get(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]))
            ->assertOk()
            ->assertSee($transaction->order_id)
            ->assertDontSee('Produk Rahasia Pelacakan')
            ->assertSee('Lacak Pesanan');
    }

    public function test_guest_can_verify_and_view_order_with_matching_email_and_order_id(): void
    {
        $transaction = $this->makeTransaction();

        $response = $this->post(route('frontend.order-tracking.verify'), [
            'email' => 'TRACK@EXAMPLE.TEST',
            'order_id' => strtolower($transaction->order_id),
        ]);

        $response
            ->assertRedirect(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]))
            ->assertSessionHas('verified_orders', fn (array $orders) => in_array($transaction->order_id, $orders, true));

        $this->get(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]))
            ->assertOk()
            ->assertSee('Order terverifikasi')
            ->assertSee('Produk Rahasia Pelacakan')
            ->assertSee(route('manual-payment.proof', $transaction), false)
            ->assertSee('Upload bukti transfer')
            ->assertSee('Buat Akun Saya')
            ->assertSee(route('register', ['checkout_order' => $transaction->order_id]), false);
    }

    public function test_member_order_can_be_verified_using_account_email(): void
    {
        $user = User::factory()->create(['email' => 'member-track@example.test']);
        $transaction = $this->makeTransaction([
            'user_id' => $user->id,
            'manual_customer_email' => null,
        ]);

        $this->post(route('frontend.order-tracking.verify'), [
            'email' => 'MEMBER-TRACK@EXAMPLE.TEST',
            'order_id' => $transaction->order_id,
        ])->assertRedirect(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]));

        $this->get(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]))
            ->assertSee('Produk Rahasia Pelacakan');
    }

    public function test_incorrect_tracking_pair_returns_generic_error_without_disclosure(): void
    {
        $transaction = $this->makeTransaction();

        $this->from(route('frontend.order-tracking.index'))
            ->post(route('frontend.order-tracking.verify'), [
                'email' => 'wrong@example.test',
                'order_id' => $transaction->order_id,
            ])
            ->assertRedirect(route('frontend.order-tracking.index'))
            ->assertSessionHasErrors([
                'tracking' => 'Email atau nomor order tidak cocok. Periksa kembali data yang dimasukkan.',
            ])
            ->assertSessionMissing('verified_orders');
    }

    public function test_tracking_attempts_are_rate_limited_after_three_failures(): void
    {
        $email = 'limited@example.test';
        $key = 'order-tracking:'.hash('sha256', '127.0.0.1|'.$email);
        RateLimiter::clear($key);

        $payload = ['email' => $email, 'order_id' => 'MAN-NOT-FOUND'];

        $this->post(route('frontend.order-tracking.verify'), $payload)->assertRedirect();
        $this->post(route('frontend.order-tracking.verify'), $payload)->assertRedirect();
        $this->post(route('frontend.order-tracking.verify'), $payload)
            ->assertStatus(429)
            ->assertSessionHasErrors('tracking');

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 3));
    }

    public function test_verified_order_with_uploaded_proof_shows_verification_state(): void
    {
        $transaction = $this->makeTransaction([
            'status' => 'menunggu_verifikasi',
            'payment_proof_path' => 'storage/payment-proofs/example.jpg',
            'payment_proof_uploaded_at' => now(),
        ]);

        $this->withSession(['verified_orders' => [$transaction->order_id]])
            ->get(route('frontend.order-tracking.index', ['order_id' => $transaction->order_id]))
            ->assertOk()
            ->assertSee('Bukti pembayaran sudah diterima')
            ->assertDontSee('name="payment_proof"', false);
    }

    public function test_fresh_guest_session_can_verify_from_tracking_then_upload_proof(): void
    {
        Storage::fake('public');
        $transaction = $this->makeTransaction();

        $this->post(route('frontend.order-tracking.verify'), [
            'email' => 'track@example.test',
            'order_id' => $transaction->order_id,
        ])->assertRedirect();

        $this->post(route('manual-payment.proof', $transaction), [
            'payment_proof' => UploadedFile::fake()->image('tracking-proof.jpg', 100, 100),
        ])->assertRedirect();

        $this->assertNotEmpty($transaction->fresh()->payment_proof_path);
    }
}
