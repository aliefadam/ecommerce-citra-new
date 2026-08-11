<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ReturnRequest;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnRefundFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_request_partial_refund_and_admin_can_approve_it_idempotently(): void
    {
        $owner = User::factory()->create();
        [$transaction, $detail] = $this->makeShippedTransaction($owner, quantity: 2, price: 75000);

        $this->actingAs($owner)
            ->post(route('frontend.profil.return-requests.store'), [
                'transaction_id' => $transaction->id,
                'type' => 'refund',
                'reason' => 'Satu barang rusak saat diterima.',
                'customer_note' => 'Mohon refund satu item.',
                'items' => [$detail->id => 1],
            ])
            ->assertRedirect(route('frontend.profil', ['tab' => 'pesanan']))
            ->assertSessionHas('success');

        $returnRequest = ReturnRequest::query()->firstOrFail();
        $this->assertSame($owner->id, $returnRequest->user_id);
        $this->assertSame('menunggu', $returnRequest->status);
        $this->assertSame(75000, (int) $returnRequest->refund_amount);
        $this->assertDatabaseHas('return_request_items', [
            'return_request_id' => $returnRequest->id,
            'transaction_detail_id' => $detail->id,
            'quantity' => 1,
            'subtotal' => 75000,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->patch(route('return-requests.update', $returnRequest), [
                'status' => 'disetujui',
                'admin_note' => 'Refund disetujui, transfer dilakukan manual.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $approvedAt = $returnRequest->fresh()->approved_at;
        $this->assertNotNull($approvedAt);
        $this->assertDatabaseCount('user_notifications', 1);

        $this->patch(route('return-requests.update', $returnRequest), [
            'status' => 'disetujui',
            'admin_note' => 'Refund disetujui, transfer dilakukan manual.',
        ])->assertRedirect();

        $this->assertTrue($approvedAt->equalTo($returnRequest->fresh()->approved_at));
        $this->assertDatabaseCount('user_notifications', 1);
    }

    public function test_customer_cannot_request_refund_for_another_customers_order(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        [$transaction, $detail] = $this->makeShippedTransaction($owner);

        $this->actingAs($attacker)
            ->post(route('frontend.profil.return-requests.store'), [
                'transaction_id' => $transaction->id,
                'type' => 'refund',
                'reason' => 'Bukan order saya.',
                'items' => [$detail->id => 1],
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('return_requests', 0);
    }

    public function test_refund_quantity_cannot_exceed_remaining_returnable_quantity(): void
    {
        $owner = User::factory()->create();
        [$transaction, $detail] = $this->makeShippedTransaction($owner, quantity: 2, price: 50000);
        $this->actingAs($owner);

        $this->post(route('frontend.profil.return-requests.store'), [
            'transaction_id' => $transaction->id,
            'type' => 'refund',
            'reason' => 'Item pertama rusak.',
            'items' => [$detail->id => 1],
        ])->assertRedirect()->assertSessionHas('success');

        $this->from(route('frontend.profil', ['tab' => 'pesanan']))
            ->post(route('frontend.profil.return-requests.store'), [
                'transaction_id' => $transaction->id,
                'type' => 'refund',
                'reason' => 'Mencoba melebihi sisa quantity.',
                'items' => [$detail->id => 2],
            ])
            ->assertRedirect(route('frontend.profil', ['tab' => 'pesanan']))
            ->assertSessionHasErrors('return_request');

        $this->assertDatabaseCount('return_requests', 1);
        $this->assertSame(1, (int) $detail->returnRequestItems()->sum('quantity'));
    }

    public function test_order_outside_seven_day_window_is_not_eligible_for_return(): void
    {
        $owner = User::factory()->create();
        [$transaction, $detail] = $this->makeShippedTransaction($owner);
        $transaction->forceFill(['shipped_at' => now()->subDays(8)])->save();

        $this->actingAs($owner)
            ->post(route('frontend.profil.return-requests.store'), [
                'transaction_id' => $transaction->id,
                'type' => 'refund',
                'reason' => 'Sudah melewati batas waktu.',
                'items' => [$detail->id => 1],
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('return_request');

        $this->assertDatabaseCount('return_requests', 0);
    }

    private function makeShippedTransaction(User $owner, int $quantity = 1, int $price = 100000): array
    {
        $company = Company::query()->firstOrFail();
        $suffix = strtoupper(fake()->bothify('####??'));

        $transaction = Transaction::create([
            'company_id' => $company->id,
            'user_id' => $owner->id,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-RETURN-'.$suffix,
            'order_id' => 'ORD-RETURN-'.$suffix,
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
            'payment_status' => 'paid',
            'status' => 'kirim',
            'subtotal_amount' => $price * $quantity,
            'shipping_cost' => 0,
            'grand_total' => $price * $quantity,
            'paid_at' => now()->subDay(),
            'shipped_at' => now()->subDay(),
        ]);

        $detail = $transaction->details()->create([
            'product_name' => 'Produk Return Test',
            'variant_name' => 'Standard',
            'price' => $price,
            'quantity' => $quantity,
            'subtotal' => $price * $quantity,
        ]);

        return [$transaction, $detail];
    }
}
