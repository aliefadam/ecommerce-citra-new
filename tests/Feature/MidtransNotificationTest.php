<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class MidtransNotificationTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'midtrans-notification-test-key';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', self::SERVER_KEY);
        Config::set('services.midtrans.is_production', false);
    }

    public function test_invalid_signature_is_rejected_without_changing_transaction(): void
    {
        $transaction = $this->makeTransaction();
        $payload = $this->notificationPayload($transaction, 'settlement');
        $payload['signature_key'] = str_repeat('0', 128);

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertUnauthorized()
            ->assertJson(['message' => 'Signature tidak valid.']);

        $this->assertSame('pending', $transaction->fresh()->status);
        $this->assertDatabaseCount('transaction_status_histories', 0);
        $this->assertDatabaseCount('user_notifications', 0);
    }

    public function test_valid_settlement_is_idempotent_and_records_payment_once(): void
    {
        $user = User::factory()->create();
        $transaction = $this->makeTransaction($user);
        $payload = $this->notificationPayload($transaction, 'settlement', [
            'transaction_id' => 'provider-transaction-001',
            'payment_type' => 'bank_transfer',
        ]);

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);
        $firstPaidAt = $transaction->fresh()->paid_at;

        $this->postJson(route('midtrans.notification'), $payload)
            ->assertOk()
            ->assertJson(['ok' => true]);

        $transaction->refresh();
        $this->assertSame('settlement', $transaction->status);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertSame(125000, (int) $transaction->payment_amount);
        $this->assertSame('provider-transaction-001', $transaction->midtrans_transaction_id);
        $this->assertSame('bank_transfer', $transaction->payment_type);
        $this->assertNotNull($transaction->payment_paid_at);
        $this->assertTrue($firstPaidAt->equalTo($transaction->paid_at));

        $this->assertDatabaseCount('transaction_status_histories', 1);
        $this->assertDatabaseCount('user_notifications', 1);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $transaction->id,
            'from_status' => 'pending',
            'to_status' => 'settlement',
            'type' => 'payment_status_sync',
        ]);
    }

    public function test_out_of_order_pending_and_cancel_notifications_cannot_downgrade_paid_order(): void
    {
        $transaction = $this->makeTransaction();

        $this->postJson(
            route('midtrans.notification'),
            $this->notificationPayload($transaction, 'settlement')
        )->assertOk();

        $this->postJson(
            route('midtrans.notification'),
            $this->notificationPayload($transaction, 'pending')
        )->assertOk();

        $this->postJson(
            route('midtrans.notification'),
            $this->notificationPayload($transaction, 'expire')
        )->assertOk();

        $transaction->refresh();
        $this->assertSame('settlement', $transaction->status);
        $this->assertSame('paid', $transaction->payment_status);
        $this->assertNull($transaction->cancelled_at);
        $this->assertDatabaseCount('transaction_status_histories', 1);
    }

    public function test_cancelled_order_cannot_return_to_pending_from_late_notification(): void
    {
        $transaction = $this->makeTransaction();

        $this->postJson(
            route('midtrans.notification'),
            $this->notificationPayload($transaction, 'expire')
        )->assertOk();

        $this->postJson(
            route('midtrans.notification'),
            $this->notificationPayload($transaction, 'pending')
        )->assertOk();

        $transaction->refresh();
        $this->assertSame('dibatalkan', $transaction->status);
        $this->assertSame('cancelled', $transaction->payment_status);
        $this->assertNotNull($transaction->cancelled_at);
        $this->assertDatabaseCount('transaction_status_histories', 1);
    }

    public function test_capture_with_fraud_challenge_does_not_mark_order_paid(): void
    {
        $transaction = $this->makeTransaction();

        $this->postJson(route('midtrans.notification'), $this->notificationPayload(
            $transaction,
            'capture',
            ['fraud_status' => 'challenge']
        ))->assertOk();

        $transaction->refresh();
        $this->assertSame('pending', $transaction->status);
        $this->assertSame('unpaid', $transaction->payment_status);
        $this->assertNull($transaction->paid_at);
        $this->assertDatabaseCount('transaction_status_histories', 0);
    }

    private function makeTransaction(?User $user = null): Transaction
    {
        $company = Company::query()->firstOrFail();

        return Transaction::create([
            'company_id' => $company->id,
            'user_id' => $user?->id,
            'source' => Transaction::SOURCE_CHECKOUT,
            'invoice_no' => 'INV-MID-'.strtoupper(fake()->bothify('####??')),
            'order_id' => 'ORD-MID-'.strtoupper(fake()->bothify('####??')),
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA Virtual Account',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal_amount' => 115000,
            'shipping_cost' => 10000,
            'grand_total' => 125000,
            'expires_at' => now()->addMinutes(30),
        ]);
    }

    private function notificationPayload(Transaction $transaction, string $status, array $overrides = []): array
    {
        $payload = array_merge([
            'order_id' => $transaction->order_id,
            'status_code' => '200',
            'gross_amount' => '125000.00',
            'transaction_status' => $status,
            'transaction_id' => 'provider-'.$transaction->order_id,
            'payment_type' => 'bank_transfer',
        ], $overrides);

        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].self::SERVER_KEY,
        );

        return $payload;
    }
}
