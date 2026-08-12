<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestPaymentProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_payment_proof_requires_order_ownership(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('payment-proofs/proof.webp', 'private-proof');
        $transaction = $this->makeManualTransaction([
            'payment_proof_path' => 'private/payment-proofs/proof.webp',
        ]);

        $this->get(route('payment-proof.show', $transaction))->assertForbidden();

        $response = $this->withSession(['verified_orders' => [$transaction->order_id]])
            ->get(route('payment-proof.show', $transaction))
            ->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
    }

    private function makeManualTransaction(array $overrides = []): Transaction
    {
        return Transaction::create(array_merge([
            'company_id' => Company::query()->value('id'),
            'user_id' => null,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-GUEST-'.uniqid(),
            'order_id' => 'MAN-GUEST-'.strtoupper(uniqid()),
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
            'status' => 'pending',
            'subtotal_amount' => 100000,
            'shipping_cost' => 10000,
            'grand_total' => 110000,
            'shipping_recipient_name' => 'Guest Proof',
            'manual_customer_name' => 'Guest Proof',
            'manual_customer_email' => 'proof@example.test',
        ], $overrides));
    }

    public function test_guest_can_upload_proof_for_order_owned_by_session(): void
    {
        Storage::fake('public');
        $transaction = $this->makeManualTransaction();

        $this->withSession(['guest_owned_orders' => [$transaction->order_id]])
            ->post(route('manual-payment.proof', $transaction), [
                'payment_proof' => UploadedFile::fake()->image('proof.jpg', 80, 80),
            ])
            ->assertRedirect();

        $transaction->refresh();
        $this->assertNotEmpty($transaction->payment_proof_path);
        $this->assertSame('menunggu_verifikasi', $transaction->status);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $transaction->id,
            'user_id' => null,
            'type' => 'payment_proof_uploaded',
        ]);
    }

    public function test_verified_order_session_can_upload_proof_without_login(): void
    {
        Storage::fake('public');
        $transaction = $this->makeManualTransaction();

        $this->withSession(['verified_orders' => [$transaction->order_id]])
            ->post(route('manual-payment.proof', $transaction), [
                'payment_proof' => UploadedFile::fake()->image('verified-proof.png', 80, 80),
            ])
            ->assertRedirect();

        $this->assertNotEmpty($transaction->fresh()->payment_proof_path);
    }

    public function test_guest_cannot_upload_proof_for_another_order(): void
    {
        Storage::fake('public');
        $transaction = $this->makeManualTransaction();

        $this->withSession(['guest_owned_orders' => ['MAN-DIFFERENT-ORDER']])
            ->post(route('manual-payment.proof', $transaction), [
                'payment_proof' => UploadedFile::fake()->image('foreign.jpg', 80, 80),
            ])
            ->assertForbidden();

        $this->assertNull($transaction->fresh()->payment_proof_path);
    }

    public function test_authenticated_owner_can_still_upload_payment_proof(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $transaction = $this->makeManualTransaction(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('manual-payment.proof', $transaction), [
                'payment_proof' => UploadedFile::fake()->image('member.jpg', 80, 80),
            ])
            ->assertRedirect();

        $this->assertNotEmpty($transaction->fresh()->payment_proof_path);
    }

    public function test_paid_order_cannot_be_returned_to_verification_by_uploading_again(): void
    {
        Storage::fake('public');
        $transaction = $this->makeManualTransaction(['status' => 'paid']);

        $this->withSession(['guest_owned_orders' => [$transaction->order_id]])
            ->post(route('manual-payment.proof', $transaction), [
                'payment_proof' => UploadedFile::fake()->image('late-proof.jpg', 80, 80),
            ])
            ->assertStatus(422);

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertNull($transaction->fresh()->payment_proof_path);
    }

    public function test_guest_waiting_page_shows_upload_form_without_member_controls(): void
    {
        $transaction = $this->makeManualTransaction();

        $this->withSession([
            'checkout' => ['source' => 'buy_now'],
            'guest_owned_orders' => [$transaction->order_id],
        ])->get(route('frontend.checkout.waiting', $transaction->order_id))
            ->assertOk()
            ->assertSee('Upload Bukti Transfer', false)
            ->assertDontSee('Simulasi', false)
            ->assertSee('Buat Akun Saya', false)
            ->assertSee('/register?checkout_order=', false)
            ->assertSee('/lacak-pesanan?order_id=', false);
    }

    public function test_guest_invoice_email_contains_track_order_button(): void
    {
        $transaction = $this->makeManualTransaction();

        $html = view('emails.invoice-order', [
            'transaction' => $transaction->load('details', 'user'),
        ])->render();

        $this->assertStringContainsString('Lihat Status Pesanan', $html);
        $this->assertStringContainsString('/lacak-pesanan?order_id='.$transaction->order_id, $html);
        $this->assertStringContainsString('Guest Proof', $html);
    }
}
