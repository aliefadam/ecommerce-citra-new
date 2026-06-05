<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionsUsabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactions_index_exposes_usability_controls(): void
    {
        $this->actingAs($this->makeAdminUser());

        $this->makeTransaction([
            'invoice_no' => 'INV-MANUAL',
            'order_id' => 'ORD-MANUAL',
            'status' => 'menunggu_verifikasi',
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
        ]);
        $this->makeTransaction([
            'invoice_no' => 'INV-PAID',
            'order_id' => 'ORD-PAID',
            'status' => 'paid',
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA',
            'payment_va_bank' => 'bca',
        ]);
        $this->makeTransaction([
            'invoice_no' => 'INV-PROCESS',
            'order_id' => 'ORD-PROCESS',
            'status' => 'process',
            'payment_type' => 'qris',
            'payment_method' => 'QRIS',
        ]);

        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertSee('txStatusFilters', false);
        $response->assertSee('txSummaryCards', false);
        $response->assertSee('txBulkToolbar', false);
        $response->assertSee('txSelectAllPage', false);
        $response->assertSee('Tanggal', false);
        $response->assertSee('Pembayaran', false);
        $response->assertSee('Menunggu Verifikasi', false);
        $response->assertSee('Print Resi Terpilih', false);
        $response->assertSee('getTxPrimaryAction', false);
        $response->assertSee('txBulkPrintIssues', false);
        $response->assertSee('bulkShippingLabelUrl', false);
    }

    public function test_manual_transfer_waiting_verification_can_be_approved(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'status' => 'menunggu_verifikasi',
            'payment_type' => 'manual_transfer',
            'payment_method' => 'Transfer Manual',
        ]);

        $response = $this->from(route('transactions.index'))->patch(route('transactions.verify-payment', $transaction), [
            'action' => 'approve',
            'payment_admin_note' => 'OK',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertSame('paid', $transaction->fresh()->status);
    }

    public function test_paid_transaction_can_be_processed(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction(['status' => 'paid']);

        $response = $this->patchJson(route('transactions.process', $transaction));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame('process', $transaction->fresh()->status);
    }

    public function test_process_transaction_can_be_shipped(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction(['status' => 'process']);

        $response = $this->patchJson(route('transactions.ship', $transaction), [
            'tracking_number' => 'RESI-123',
            'shipping_label' => 'JNE REG',
            'shipping_note' => 'Fragile',
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $fresh = $transaction->fresh();
        $this->assertSame('kirim', $fresh->status);
        $this->assertSame('RESI-123', $fresh->tracking_number);
    }

    public function test_single_shipping_label_still_renders(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'invoice_no' => 'INV-SINGLE',
            'order_id' => 'ORD-SINGLE',
            'status' => 'kirim',
            'tracking_number' => 'RESI-SINGLE',
        ]);

        $response = $this->get(route('transactions.shipping-label', $transaction));

        $response->assertOk();
        $response->assertSee('RESI : RESI-SINGLE', false);
        $response->assertSee('ORD-SINGLE', false);
    }

    public function test_bulk_shipping_labels_render_multiple_valid_transactions(): void
    {
        $this->actingAs($this->makeAdminUser());
        $first = $this->makeTransaction([
            'invoice_no' => 'INV-BULK-1',
            'order_id' => 'ORD-BULK-1',
            'status' => 'kirim',
            'tracking_number' => 'RESI-BULK-1',
        ]);
        $second = $this->makeTransaction([
            'invoice_no' => 'INV-BULK-2',
            'order_id' => 'ORD-BULK-2',
            'status' => 'process',
            'tracking_number' => '',
        ]);

        $response = $this->get(route('transactions.bulk-shipping-label', [
            'ids' => $first->id . ',' . $second->id,
        ]));

        $response->assertOk();
        $response->assertSee('RESI : RESI-BULK-1', false);
        $response->assertSee('RESI : ORD-BULK-2', false);
        $response->assertDontSee('transaksi tidak bisa dicetak', false);
    }

    public function test_bulk_shipping_labels_show_invalid_transactions_without_error(): void
    {
        $this->actingAs($this->makeAdminUser());
        $valid = $this->makeTransaction([
            'invoice_no' => 'INV-VALID',
            'order_id' => 'ORD-VALID',
            'status' => 'kirim',
            'tracking_number' => 'RESI-VALID',
        ]);
        $missingAddress = $this->makeTransaction([
            'invoice_no' => 'INV-NO-ADDRESS',
            'order_id' => 'ORD-NO-ADDRESS',
            'shipping_recipient_name' => '',
            'shipping_phone' => '',
            'shipping_address_line' => '',
        ]);
        $missingTracking = $this->makeTransaction([
            'invoice_no' => 'INV-NO-RESI',
            'order_id' => 'ORD-NO-RESI',
            'status' => 'kirim',
            'tracking_number' => '',
        ]);

        $response = $this->get(route('transactions.bulk-shipping-label', [
            'ids' => $valid->id . ',' . $missingAddress->id . ',' . $missingTracking->id,
        ]));

        $response->assertOk();
        $response->assertSee('RESI : RESI-VALID', false);
        $response->assertSee('INV-NO-ADDRESS', false);
        $response->assertSee('Alamat pengiriman belum lengkap.', false);
        $response->assertSee('INV-NO-RESI', false);
        $response->assertSee('Nomor resi belum ada.', false);
    }

    private function makeAdminUser(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeTransaction(array $overrides = []): Transaction
    {
        $customer = User::factory()->create();

        $transaction = Transaction::create(array_merge([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-' . uniqid(),
            'order_id' => 'ORD-' . uniqid(),
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA',
            'payment_va_bank' => 'bca',
            'status' => 'pending',
            'subtotal_amount' => 100000,
            'shipping_cost' => 10000,
            'grand_total' => 110000,
            'shipping_label' => 'JNE REG',
            'shipping_recipient_name' => 'Customer Test',
            'shipping_phone' => '08123456789',
            'shipping_address_line' => 'Jl. Testing No. 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ], $overrides));

        TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'product_name' => 'Produk Test',
            'variant_name' => 'Default',
            'price' => 100000,
            'quantity' => 1,
            'subtotal' => 100000,
        ]);

        return $transaction;
    }
}
