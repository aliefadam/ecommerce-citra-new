<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Company;
use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\ShipmentTrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileAccountApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['mobile-spa.enabled' => true]);
        $this->seed();
    }

    public function test_account_and_order_endpoints_require_member_session(): void
    {
        $this->getJson('/api/mobile/v1/account')->assertUnauthorized();
        $this->getJson('/api/mobile/v1/orders')->assertUnauthorized();
        $this->getJson('/api/mobile/v1/notifications')->assertUnauthorized();
    }

    public function test_member_sees_only_owned_orders_with_masked_sensitive_data_and_state_actions(): void
    {
        $user = User::factory()->create();
        $owned = $this->makeTransaction($user, ['status' => 'kirim', 'shipping_phone' => '+628123456789']);
        $other = $this->makeTransaction(User::factory()->create());
        TransactionTaxInvoice::query()->create([
            'transaction_id' => $owned->id,
            'requested_by_user_id' => $user->id,
            'status' => 'issued',
            'taxpayer_name' => 'PT Mobile',
            'taxpayer_number' => '1234567890123456',
            'taxpayer_address' => 'Jakarta',
            'taxpayer_email' => 'tax@example.test',
        ]);
        $this->mock(ShipmentTrackingService::class)
            ->shouldReceive('forTransaction')
            ->once()
            ->andReturn(['available' => true, 'awb' => 'JNE123', 'events' => []]);

        $this->actingAs($user)->getJson('/api/mobile/v1/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_id', $owned->order_id)
            ->assertJsonMissing(['order_id' => $other->order_id]);

        $this->getJson('/api/mobile/v1/orders/'.$owned->order_id)
            ->assertOk()
            ->assertJsonPath('data.shipping.phone', '+628******789')
            ->assertJsonPath('data.actions.can_complete', true)
            ->assertJsonPath('data.actions.can_pay', false)
            ->assertJsonPath('data.tax_invoice.taxpayer_number', '1234********3456')
            ->assertJsonPath('data.tracking.awb', 'JNE123')
            ->assertJsonMissing(['taxpayer_number' => '1234567890123456']);

        $this->getJson('/api/mobile/v1/orders/'.$other->order_id)->assertNotFound();
    }

    public function test_account_profile_includes_avatar_url(): void
    {
        $user = User::factory()->create(['avatar' => 'https://example.test/avatar.webp']);

        $this->actingAs($user)->getJson('/api/mobile/v1/account')
            ->assertOk()
            ->assertJsonPath('data.profile.avatar', 'https://example.test/avatar.webp');
    }

    public function test_profile_password_and_addresses_are_owned_and_validated(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        $address = $this->makeAddress($user);
        $otherAddress = $this->makeAddress(User::factory()->create());
        $this->actingAs($user)
            ->patchJson('/api/mobile/v1/account/profile', [
                'first_name' => 'Mobile',
                'last_name' => 'Member',
                'email' => 'MOBILE.MEMBER@EXAMPLE.TEST',
                'phone_country_code' => '+62',
                'phone_number' => '8123456789',
            ])
            ->assertOk()
            ->assertJsonPath('data.email', 'mobile.member@example.test');

        $this->patchJson('/api/mobile/v1/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->patchJson('/api/mobile/v1/account/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        $this->patchJson('/api/mobile/v1/addresses/'.$otherAddress->id, $this->addressPayload())
            ->assertNotFound();
        $this->deleteJson('/api/mobile/v1/addresses/'.$otherAddress->id)->assertNotFound();
        $this->patchJson('/api/mobile/v1/addresses/'.$address->id, [
            ...$this->addressPayload(),
            'recipient_name' => 'Alamat Diperbarui',
        ])->assertOk()->assertJsonPath('data.recipient_name', 'Alamat Diperbarui');
    }

    public function test_shipped_order_completion_is_state_guarded_and_idempotency_is_enforced_by_lock(): void
    {
        $user = User::factory()->create();
        $shipped = $this->makeTransaction($user, ['status' => 'kirim']);
        $pending = $this->makeTransaction($user, ['status' => 'pending']);
        $this->actingAs($user)
            ->postJson('/api/mobile/v1/orders/'.$pending->order_id.'/complete')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('order');

        $this->postJson('/api/mobile/v1/orders/'.$shipped->order_id.'/complete')
            ->assertOk()
            ->assertJsonPath('data.status', 'selesai')
            ->assertJsonPath('data.actions.can_complete', false);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $shipped->id,
            'to_status' => 'selesai',
            'type' => 'order_completed',
        ]);
        $this->postJson('/api/mobile/v1/orders/'.$shipped->order_id.'/complete')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('order');
        $this->assertDatabaseCount('transaction_status_histories', 1);
    }

    public function test_tax_invoice_and_notifications_are_scoped_and_masked(): void
    {
        $user = User::factory()->create();
        $transaction = $this->makeTransaction($user, ['status' => 'selesai']);
        $otherNotification = UserNotification::query()->create([
            'user_id' => User::factory()->create()->id,
            'type' => 'order',
            'title' => 'Rahasia User Lain',
            'body' => 'Tidak boleh terlihat',
        ]);
        $notification = UserNotification::query()->create([
            'user_id' => $user->id,
            'type' => 'order',
            'title' => 'Pesanan dikirim',
            'body' => 'Pesanan sedang dikirim.',
        ]);
        $this->actingAs($user)->postJson('/api/mobile/v1/orders/'.$transaction->order_id.'/tax-invoice', [
            'taxpayer_name' => 'PT Fase Lima',
            'taxpayer_number' => '1234567890123456',
            'taxpayer_address' => 'Jakarta',
            'taxpayer_email' => 'pajak@example.test',
        ])->assertCreated()->assertJsonPath('data.taxpayer_number', '1234********3456');

        $this->getJson('/api/mobile/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.unread', 1)
            ->assertJsonMissing(['id' => $otherNotification->id]);
        $this->postJson('/api/mobile/v1/notifications/'.$otherNotification->id.'/read')->assertNotFound();
        $this->postJson('/api/mobile/v1/notifications/'.$notification->id.'/read')->assertOk();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_order_filters_include_equivalent_domain_statuses_and_cancelled_order_rejects_tax_invoice(): void
    {
        $user = User::factory()->create();
        $waiting = $this->makeTransaction($user, ['status' => 'menunggu_verifikasi']);
        $paid = $this->makeTransaction($user, ['status' => 'paid']);
        $failed = $this->makeTransaction($user, ['status' => 'failure']);
        $this->actingAs($user)->getJson('/api/mobile/v1/orders?status=pending')
            ->assertOk()
            ->assertJsonFragment(['order_id' => $waiting->order_id])
            ->assertJsonMissing(['order_id' => $paid->order_id]);
        $this->getJson('/api/mobile/v1/orders?status=process')
            ->assertOk()
            ->assertJsonFragment(['order_id' => $paid->order_id]);
        $this->getJson('/api/mobile/v1/orders?status=dibatalkan')
            ->assertOk()
            ->assertJsonFragment(['order_id' => $failed->order_id]);

        $this->postJson('/api/mobile/v1/orders/'.$failed->order_id.'/tax-invoice', [
            'taxpayer_name' => 'PT Tidak Valid',
            'taxpayer_number' => '1234567890123456',
            'taxpayer_address' => 'Jakarta',
            'taxpayer_email' => 'invalid@example.test',
        ])->assertUnprocessable()->assertJsonValidationErrors('order');
        $this->assertDatabaseMissing('transaction_tax_invoices', ['transaction_id' => $failed->id]);
    }

    private function makeTransaction(User $user, array $overrides = []): Transaction
    {
        $transaction = Transaction::query()->create(array_merge([
            'company_id' => Company::query()->value('id'),
            'user_id' => $user->id,
            'invoice_no' => 'INV-MOBILE-'.uniqid(),
            'order_id' => 'ORD-MOBILE-'.strtoupper(uniqid()),
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
            'status' => 'pending',
            'subtotal_amount' => 100_000,
            'shipping_cost' => 10_000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 110_000,
            'shipping_recipient_name' => 'Mobile Member',
            'shipping_phone' => '+628123456789',
            'shipping_address_line' => 'Jl. Mobile No. 5',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'tracking_number' => 'JNE123',
        ], $overrides));
        $transaction->details()->create([
            'product_name' => 'Produk Fase Lima',
            'variant_name' => 'Standar',
            'sku' => 'PHASE-5',
            'price' => 100_000,
            'quantity' => 1,
            'subtotal' => 100_000,
        ]);

        return $transaction;
    }

    private function makeAddress(User $user): Address
    {
        return Address::query()->create(['user_id' => $user->id, ...$this->addressPayload()]);
    }

    private function addressPayload(): array
    {
        return [
            'label' => 'Rumah',
            'recipient_name' => 'Mobile Member',
            'phone_country_code' => '+62',
            'phone_number' => '8123456789',
            'province_id' => 1,
            'city_id' => 2,
            'district_id' => 3,
            'subdistrict_id' => 4,
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Setiabudi',
            'subdistrict' => 'Karet',
            'postal_code' => '12920',
            'destination_id' => 99,
            'address_line' => 'Jl. Mobile No. 5',
            'is_primary' => true,
        ];
    }
}
