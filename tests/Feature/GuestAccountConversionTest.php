<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccountConversionTest extends TestCase
{
    use RefreshDatabase;

    private function makeGuestTransaction(array $overrides = []): Transaction
    {
        $transaction = Transaction::create(array_merge([
            'company_id' => Company::query()->value('id'),
            'user_id' => null,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-CONVERT-'.uniqid(),
            'order_id' => 'MAN-CONVERT-'.strtoupper(uniqid()),
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
            'status' => 'pending',
            'subtotal_amount' => 75000,
            'shipping_cost' => 5000,
            'grand_total' => 80000,
            'manual_customer_name' => 'Guest Menjadi Member',
            'manual_customer_email' => 'convert@example.test',
            'shipping_recipient_name' => 'Guest Menjadi Member',
        ], $overrides));

        $transaction->details()->create([
            'product_name' => 'Produk Konversi Guest',
            'price' => 75000,
            'quantity' => 1,
            'subtotal' => 75000,
        ]);

        return $transaction;
    }

    public function test_owned_guest_order_opens_prefilled_account_form(): void
    {
        $transaction = $this->makeGuestTransaction();

        $this->withSession(['guest_owned_orders' => [$transaction->order_id]])
            ->get(route('register', ['checkout_order' => $transaction->order_id]))
            ->assertOk()
            ->assertSee('Simpan Pesananmu')
            ->assertSee($transaction->order_id)
            ->assertSee('Guest Menjadi Member')
            ->assertSee('convert@example.test')
            ->assertSee('Buat Akun &amp; Simpan Pesanan', false);
    }

    public function test_unknown_order_query_does_not_expose_guest_data(): void
    {
        $transaction = $this->makeGuestTransaction();

        $this->get(route('register', ['checkout_order' => $transaction->order_id]))
            ->assertOk()
            ->assertSee('Register')
            ->assertDontSee($transaction->order_id)
            ->assertDontSee('convert@example.test');
    }

    public function test_account_creation_links_all_owned_orders_with_matching_email(): void
    {
        $first = $this->makeGuestTransaction();
        $second = $this->makeGuestTransaction();
        $differentEmail = $this->makeGuestTransaction(['manual_customer_email' => 'other@example.test']);
        $notOwned = $this->makeGuestTransaction();

        $response = $this->withSession([
            'guest_owned_orders' => [$first->order_id, $second->order_id, $differentEmail->order_id],
        ])->post(route('register.attempt'), [
            'name' => 'Member Baru',
            'email' => 'CONVERT@EXAMPLE.TEST',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'checkout_order' => $first->order_id,
        ]);

        $user = User::query()->where('email', 'convert@example.test')->firstOrFail();

        $response
            ->assertRedirect(route('frontend.profil', ['tab' => 'pesanan']))
            ->assertSessionHas('success', 'Akun berhasil dibuat. 2 pesanan sudah tersimpan di akun Anda.')
            ->assertSessionMissing('guest_owned_orders');
        $this->assertAuthenticatedAs($user);
        $this->assertSame($user->id, $first->fresh()->user_id);
        $this->assertSame($user->id, $second->fresh()->user_id);
        $this->assertNull($differentEmail->fresh()->user_id);
        $this->assertNull($notOwned->fresh()->user_id);

        $this->get(route('frontend.profil', ['tab' => 'pesanan']))
            ->assertOk()
            ->assertSee($first->order_id)
            ->assertSee($second->order_id);
    }

    public function test_verified_tracking_order_can_be_linked_to_new_account(): void
    {
        $transaction = $this->makeGuestTransaction();

        $this->withSession(['verified_orders' => [$transaction->order_id]])
            ->post(route('register.attempt'), [
                'name' => 'Member Tracking',
                'email' => 'convert@example.test',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'checkout_order' => $transaction->order_id,
            ])
            ->assertRedirect(route('frontend.profil', ['tab' => 'pesanan']));

        $this->assertNotNull($transaction->fresh()->user_id);
    }

    public function test_checkout_account_form_rejects_a_different_email(): void
    {
        $transaction = $this->makeGuestTransaction();

        $this->withSession(['guest_owned_orders' => [$transaction->order_id]])
            ->from(route('register', ['checkout_order' => $transaction->order_id]))
            ->post(route('register.attempt'), [
                'name' => 'Wrong Email',
                'email' => 'attacker@example.test',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
                'checkout_order' => $transaction->order_id,
            ])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.test']);
        $this->assertNull($transaction->fresh()->user_id);
    }

    public function test_unowned_order_cannot_be_claimed_during_registration(): void
    {
        $transaction = $this->makeGuestTransaction();

        $this->post(route('register.attempt'), [
            'name' => 'Unauthorized Claim',
            'email' => 'convert@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'checkout_order' => $transaction->order_id,
        ])->assertSessionHasErrors('checkout_order');

        $this->assertDatabaseMissing('users', ['email' => 'convert@example.test']);
        $this->assertNull($transaction->fresh()->user_id);
    }
}
