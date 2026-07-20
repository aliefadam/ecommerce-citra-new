<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        $response->assertSee('Source', false);
        $response->assertSee('Checkout Ecommerce', false);
        $response->assertSee('Pembayaran', false);
        $response->assertSee('Menunggu Verifikasi', false);
        $response->assertSee('Print Resi Terpilih', false);
        $response->assertSee('getTxPrimaryAction', false);
        $response->assertSee('txBulkPrintIssues', false);
        $response->assertSee('bulkShippingLabelUrl', false);
    }

    public function test_transaction_source_defaults_to_checkout(): void
    {
        $transaction = $this->makeTransaction();

        $this->assertSame('checkout', $transaction->fresh()->source);
        $this->assertSame('Checkout Ecommerce', $transaction->fresh()->source_label);
    }

    public function test_transactions_index_shows_manual_source_label(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $this->makeTransaction([
            'invoice_no' => 'INV-SOURCE-MANUAL',
            'order_id' => 'ORD-SOURCE-MANUAL',
            'source' => 'manual',
            'created_by_admin_id' => $admin->id,
        ]);

        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertSee('Manual Admin', false);
        $response->assertSee('Checkout Ecommerce', false);
    }

    public function test_transaction_detail_shows_manual_source_creator(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);

        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'created_by_admin_id' => $admin->id,
        ]);

        $response = $this->get(route('transactions.show', $transaction));

        $response->assertOk();
        $response->assertSee('Manual Admin', false);
        $response->assertSee($admin->name, false);
    }

    public function test_manual_transaction_create_form_renders_customer_product_and_totals(): void
    {
        $this->actingAs($this->makeAdminUser());
        $customer = User::factory()->create([
            'name' => 'Customer Manual',
            'email' => 'manual-customer@example.test',
        ]);
        Address::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Customer Manual',
            'phone_country_code' => '+62',
            'phone_number' => '8123456789',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta',
            'district' => 'Menteng',
            'postal_code' => '10310',
            'address_line' => 'Jl. Manual No. 1',
            'is_primary' => true,
        ]);
        $productVariant = $this->makeProductVariant();

        $response = $this->get(route('transactions.create-manual'));

        $response->assertOk();
        $response->assertSee('Buat Transaksi Manual', false);
        $response->assertSee('Manual Admin', false);
        $response->assertSee('Customer Manual', false);
        $response->assertSee('manual-customer@example.test', false);
        $response->assertSee($productVariant->product->name, false);
        $response->assertSee('manualProducts', false);
        $response->assertSee('manualCustomers', false);
        $response->assertSee('summaryGrandTotal', false);
        $response->assertSee(route('transactions.store-manual'), false);
    }

    public function test_manual_transaction_store_route_creates_transaction_and_reduces_stock(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);
        $customer = User::factory()->create();
        $productVariant = $this->makeProductVariant();

        $response = $this->from(route('transactions.create-manual'))->post(route('transactions.store-manual'), [
            'customer_mode' => 'existing',
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_variant_id' => $productVariant->id,
                    'qty' => 2,
                    'unit_price' => 125000,
                    'discount_amount' => 5000,
                    'note' => 'Kirim sebagian',
                ],
            ],
            'discount_amount' => 10000,
            'shipping_cost' => 15000,
        ]);

        $transaction = Transaction::query()->where('source', 'manual')->first();

        $this->assertNotNull($transaction);
        $response->assertRedirect(route('transactions.show', $transaction));
        $response->assertSessionHas('success');
        $this->assertSame($customer->id, $transaction->user_id);
        $this->assertSame($admin->id, $transaction->created_by_admin_id);
        $this->assertSame('pending', $transaction->status);
        $this->assertSame('manual_admin', $transaction->payment_type);
        $this->assertSame('unpaid', $transaction->payment_status);
        $this->assertSame('belum_ditentukan', $transaction->shipping_type);
        $this->assertSame(0, (int) $transaction->payment_amount);
        $this->assertSame(245000, (int) $transaction->subtotal_amount);
        $this->assertSame(10000, (int) $transaction->discount_amount);
        $this->assertSame(15000, (int) $transaction->shipping_cost);
        $this->assertSame(250000, (int) $transaction->grand_total);

        $detail = $transaction->details()->first();
        $this->assertNotNull($detail);
        $this->assertSame($productVariant->id, $detail->product_variant_id);
        $this->assertSame('Produk Manual Test', $detail->product_name);
        $this->assertSame('PMT-M', $detail->sku);
        $this->assertSame(125000, (int) $detail->price);
        $this->assertSame(5000, (int) $detail->discount_amount);
        $this->assertSame(2, (int) $detail->quantity);
        $this->assertSame(245000, (int) $detail->subtotal);
        $this->assertSame('Kirim sebagian', $detail->item_note);

        $this->assertSame(10, (int) $productVariant->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_variant_id' => $productVariant->id,
            'transaction_detail_id' => $detail->id,
            'admin_user_id' => $admin->id,
            'type' => 'out',
            'quantity' => 2,
            'stock_before' => 12,
            'stock_after' => 10,
            'source' => 'manual_transaction',
        ]);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $transaction->id,
            'user_id' => $admin->id,
            'to_status' => 'pending',
            'type' => 'manual_admin_created',
        ]);
    }

    public function test_manual_transaction_store_route_supports_multiple_products(): void
    {
        $this->actingAs($this->makeAdminUser());
        $customer = User::factory()->create();
        $firstVariant = $this->makeProductVariant();
        $secondVariant = $this->makeProductVariant([
            'product_name' => 'Produk Manual Kedua',
            'sku' => 'PMT-L',
            'variant_value' => 'L',
            'price' => 75000,
            'stock' => 8,
        ]);

        $response = $this->post(route('transactions.store-manual'), [
            'customer_mode' => 'existing',
            'customer_id' => $customer->id,
            'items' => [
                [
                    'product_variant_id' => $firstVariant->id,
                    'qty' => 1,
                    'unit_price' => 125000,
                    'discount_amount' => 25000,
                ],
                [
                    'product_variant_id' => $secondVariant->id,
                    'qty' => 3,
                    'unit_price' => 75000,
                    'discount_amount' => 0,
                    'note' => 'Item kedua',
                ],
            ],
            'discount_amount' => 5000,
            'shipping_cost' => 20000,
        ]);

        $transaction = Transaction::query()->where('source', 'manual')->latest('id')->first();

        $this->assertNotNull($transaction);
        $response->assertRedirect(route('transactions.show', $transaction));
        $this->assertSame(325000, (int) $transaction->subtotal_amount);
        $this->assertSame(5000, (int) $transaction->discount_amount);
        $this->assertSame(20000, (int) $transaction->shipping_cost);
        $this->assertSame(340000, (int) $transaction->grand_total);
        $this->assertSame(2, $transaction->details()->count());
        $this->assertSame(11, (int) $firstVariant->fresh()->stock);
        $this->assertSame(5, (int) $secondVariant->fresh()->stock);
    }

    public function test_manual_transaction_store_route_rejects_zero_quantity(): void
    {
        $this->actingAs($this->makeAdminUser());
        $productVariant = $this->makeProductVariant();

        $response = $this->from(route('transactions.create-manual'))->post(route('transactions.store-manual'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'Customer Qty',
            'manual_customer_phone' => '0814444444',
            'items' => [
                [
                    'product_variant_id' => $productVariant->id,
                    'qty' => 0,
                    'unit_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
            'discount_amount' => 0,
            'shipping_cost' => 0,
        ]);

        $response->assertRedirect(route('transactions.create-manual'));
        $response->assertSessionHasErrors('items.0.qty');
        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(12, (int) $productVariant->fresh()->stock);
    }

    public function test_manual_transaction_store_route_supports_manual_customer_snapshot(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);
        $productVariant = $this->makeProductVariant();

        $response = $this->post(route('transactions.store-manual'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'Customer Tanpa Akun',
            'manual_customer_phone' => '0811111111',
            'manual_customer_email' => 'guest@example.test',
            'items' => [
                [
                    'product_variant_id' => $productVariant->id,
                    'qty' => 1,
                    'unit_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
            'discount_amount' => 0,
            'shipping_cost' => 0,
        ]);

        $transaction = Transaction::query()->where('source', 'manual')->first();

        $this->assertNotNull($transaction);
        $response->assertRedirect(route('transactions.show', $transaction));
        $this->assertNull($transaction->user_id);
        $this->assertSame('Customer Tanpa Akun', $transaction->manual_customer_name);
        $this->assertSame('0811111111', $transaction->manual_customer_phone);
        $this->assertSame('guest@example.test', $transaction->manual_customer_email);
        $this->assertSame('Customer Tanpa Akun', $transaction->customerDisplayName());
    }

    public function test_manual_transaction_store_route_rejects_insufficient_stock(): void
    {
        $this->actingAs($this->makeAdminUser());
        $productVariant = $this->makeProductVariant();

        $response = $this->from(route('transactions.create-manual'))->post(route('transactions.store-manual'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'Customer Stok',
            'manual_customer_phone' => '0812222222',
            'items' => [
                [
                    'product_variant_id' => $productVariant->id,
                    'qty' => 20,
                    'unit_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
            'discount_amount' => 0,
            'shipping_cost' => 0,
        ]);

        $response->assertRedirect(route('transactions.create-manual'));
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('transactions', 0);
        $this->assertSame(12, (int) $productVariant->fresh()->stock);
    }

    public function test_manual_transaction_payment_can_be_marked_paid(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'created_by_admin_id' => $admin->id,
            'payment_type' => 'manual_admin',
            'payment_method' => 'Manual Admin',
            'payment_status' => 'unpaid',
            'payment_amount' => 0,
            'status' => 'pending',
            'grand_total' => 250000,
        ]);

        $response = $this->from(route('transactions.show', $transaction))->patch(route('transactions.manual-payment.update', $transaction), [
            'payment_status' => 'paid',
            'payment_method' => 'Transfer BCA',
            'payment_paid_at' => '2026-06-06 10:30:00',
            'payment_amount' => 250000,
            'payment_admin_note' => 'Lunas via admin',
        ]);

        $response->assertRedirect(route('transactions.show', $transaction));
        $fresh = $transaction->fresh();
        $this->assertSame('paid', $fresh->payment_status);
        $this->assertSame('paid', $fresh->status);
        $this->assertSame('Transfer BCA', $fresh->payment_method);
        $this->assertSame(250000, (int) $fresh->payment_amount);
        $this->assertNotNull($fresh->paid_at);
        $this->assertNotNull($fresh->payment_paid_at);
        $this->assertSame('Lunas via admin', $fresh->payment_admin_note);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $transaction->id,
            'user_id' => $admin->id,
            'from_status' => 'pending',
            'to_status' => 'paid',
            'type' => 'manual_admin_payment_update',
        ]);
    }

    public function test_manual_transaction_payment_can_be_marked_partial_without_paid_order_status(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'payment_type' => 'manual_admin',
            'payment_status' => 'unpaid',
            'payment_amount' => 0,
            'status' => 'pending',
            'grand_total' => 250000,
        ]);

        $response = $this->patch(route('transactions.manual-payment.update', $transaction), [
            'payment_status' => 'partial',
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
            'payment_admin_note' => 'DP',
        ]);

        $response->assertRedirect();
        $fresh = $transaction->fresh();
        $this->assertSame('partial', $fresh->payment_status);
        $this->assertSame('pending', $fresh->status);
        $this->assertSame(100000, (int) $fresh->payment_amount);
        $this->assertNull($fresh->paid_at);
    }

    public function test_manual_payment_update_rejects_checkout_transaction(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'checkout',
            'payment_type' => 'bank_transfer',
            'status' => 'pending',
        ]);

        $response = $this->from(route('transactions.show', $transaction))->patch(route('transactions.manual-payment.update', $transaction), [
            'payment_status' => 'paid',
            'payment_method' => 'Cash',
            'payment_amount' => 100000,
        ]);

        $response->assertRedirect(route('transactions.show', $transaction));
        $response->assertSessionHasErrors('payment');
        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_processing_paid_manual_transaction_does_not_reduce_stock_twice(): void
    {
        $this->actingAs($this->makeAdminUser());
        $productVariant = $this->makeProductVariant();

        $this->post(route('transactions.store-manual'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'Customer Proses',
            'manual_customer_phone' => '0813333333',
            'items' => [
                [
                    'product_variant_id' => $productVariant->id,
                    'qty' => 2,
                    'unit_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
            'discount_amount' => 0,
            'shipping_cost' => 0,
        ]);

        $transaction = Transaction::query()->where('source', 'manual')->latest('id')->firstOrFail();
        $this->assertSame(10, (int) $productVariant->fresh()->stock);

        $this->patch(route('transactions.manual-payment.update', $transaction), [
            'payment_status' => 'paid',
            'payment_method' => 'Cash',
            'payment_amount' => 200000,
        ]);

        $response = $this->patchJson(route('transactions.process', $transaction->fresh()));

        $response->assertOk()->assertJson(['ok' => true]);
        $this->assertSame('process', $transaction->fresh()->status);
        $this->assertSame(10, (int) $productVariant->fresh()->stock);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_manual_transaction_shipping_can_be_updated_with_address_and_tracking_number(): void
    {
        $admin = $this->makeAdminUser();
        $this->actingAs($admin);
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'created_by_admin_id' => $admin->id,
            'payment_type' => 'manual_admin',
            'subtotal_amount' => 100000,
            'discount_amount' => 10000,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'grand_total' => 90000,
        ]);

        $response = $this->from(route('transactions.show', $transaction))->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'ekspedisi_manual',
            'shipping_recipient_name' => 'Penerima Manual',
            'shipping_phone' => '08123450000',
            'shipping_address_line' => 'Jl. Kirim No. 5',
            'shipping_province' => 'Jawa Barat',
            'shipping_city' => 'Bandung',
            'shipping_district' => 'Coblong',
            'shipping_postal_code' => '40132',
            'shipping_courier_name' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_cost' => 25000,
            'tracking_number' => 'JNE123',
            'shipping_note' => 'Titip satpam',
        ]);

        $response->assertRedirect(route('transactions.show', $transaction));
        $fresh = $transaction->fresh();
        $this->assertSame('ekspedisi_manual', $fresh->shipping_type);
        $this->assertSame('Penerima Manual', $fresh->shipping_recipient_name);
        $this->assertSame('08123450000', $fresh->shipping_phone);
        $this->assertSame('Jl. Kirim No. 5', $fresh->shipping_address_line);
        $this->assertSame('Jawa Barat', $fresh->shipping_province);
        $this->assertSame('Bandung', $fresh->shipping_city);
        $this->assertSame('Coblong', $fresh->shipping_district);
        $this->assertSame('40132', $fresh->shipping_postal_code);
        $this->assertSame('JNE', $fresh->shipping_courier_name);
        $this->assertSame('REG', $fresh->shipping_service);
        $this->assertSame('JNE REG', $fresh->shipping_label);
        $this->assertSame(25000, (int) $fresh->shipping_cost);
        $this->assertSame(115000, (int) $fresh->grand_total);
        $this->assertSame('JNE123', $fresh->tracking_number);
        $this->assertSame('Titip satpam', $fresh->shipping_note);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $transaction->id,
            'user_id' => $admin->id,
            'type' => 'manual_admin_shipping_update',
        ]);
    }

    public function test_manual_transaction_shipping_requires_address_for_delivery_type(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'payment_type' => 'manual_admin',
        ]);

        $response = $this->from(route('transactions.show', $transaction))->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'dikirim',
            'shipping_cost' => 10000,
        ]);

        $response->assertRedirect(route('transactions.show', $transaction));
        $response->assertSessionHasErrors(['shipping_recipient_name', 'shipping_phone', 'shipping_address_line']);
        $this->assertSame('JNE REG', $transaction->fresh()->shipping_label);
    }

    public function test_manual_transaction_shipping_allows_pickup_without_address(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'payment_type' => 'manual_admin',
            'shipping_cost' => 10000,
            'grand_total' => 110000,
        ]);

        $response = $this->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'ambil_sendiri',
            'shipping_cost' => 0,
            'shipping_note' => 'Diambil di toko',
        ]);

        $response->assertRedirect();
        $fresh = $transaction->fresh();
        $this->assertSame('ambil_sendiri', $fresh->shipping_type);
        $this->assertSame('Ambil sendiri', $fresh->shipping_label);
        $this->assertSame(0, (int) $fresh->shipping_cost);
        $this->assertSame(100000, (int) $fresh->grand_total);
        $this->assertSame('Diambil di toko', $fresh->shipping_note);
    }

    public function test_manual_transaction_free_shipping_forces_zero_shipping_cost(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'payment_type' => 'manual_admin',
            'shipping_cost' => 10000,
            'grand_total' => 110000,
        ]);

        $response = $this->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'gratis_ongkir',
            'shipping_recipient_name' => 'Penerima Gratis',
            'shipping_phone' => '08123456789',
            'shipping_address_line' => 'Jl. Gratis Ongkir',
            'shipping_cost' => 99999,
        ]);

        $response->assertRedirect();
        $fresh = $transaction->fresh();
        $this->assertSame('gratis_ongkir', $fresh->shipping_type);
        $this->assertSame(0, (int) $fresh->shipping_cost);
        $this->assertSame(100000, (int) $fresh->grand_total);
    }

    public function test_manual_shipping_update_rejects_checkout_transaction(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'checkout',
            'payment_type' => 'bank_transfer',
        ]);

        $response = $this->from(route('transactions.show', $transaction))->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'ambil_sendiri',
            'shipping_cost' => 0,
        ]);

        $response->assertRedirect(route('transactions.show', $transaction));
        $response->assertSessionHasErrors('shipping');
        $this->assertSame('JNE REG', $transaction->fresh()->shipping_label);
    }

    public function test_manual_transaction_invoice_and_shipping_label_render(): void
    {
        $this->actingAs($this->makeAdminUser());
        $transaction = $this->makeTransaction([
            'source' => 'manual',
            'payment_type' => 'manual_admin',
            'manual_customer_name' => 'Customer Cetak',
            'manual_customer_phone' => '0815555555',
            'shipping_type' => 'ekspedisi_manual',
            'shipping_recipient_name' => 'Penerima Cetak',
            'shipping_phone' => '0815555555',
            'shipping_address_line' => 'Jl. Cetak No. 7',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
            'shipping_courier_name' => 'JNE',
            'shipping_service' => 'REG',
            'shipping_label' => 'JNE REG',
            'tracking_number' => 'CETAK123',
        ]);

        $invoiceResponse = $this->get(route('invoice.show', $transaction));
        $shippingLabelResponse = $this->get(route('transactions.shipping-label', $transaction));

        $invoiceResponse->assertOk();
        $invoiceResponse->assertSee($transaction->invoice_no, false);
        $invoiceResponse->assertSee('Penerima Cetak', false);
        $shippingLabelResponse->assertOk();
        $shippingLabelResponse->assertSee('RESI : CETAK123', false);
        $shippingLabelResponse->assertSee('Penerima Cetak', false);
    }

    public function test_existing_manual_transfer_checkout_still_creates_checkout_transaction_and_clears_selected_cart(): void
    {
        Mail::fake();
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $productVariant = $this->makeProductVariant();
        $cart = Cart::create([
            'user_id' => $customer->id,
            'product_variant_id' => $productVariant->id,
            'quantity' => 1,
        ]);
        $address = Address::create([
            'user_id' => $customer->id,
            'label' => 'Rumah',
            'recipient_name' => 'Customer Checkout',
            'phone_country_code' => '+62',
            'phone_number' => '812999999',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta',
            'district' => 'Menteng',
            'postal_code' => '10310',
            'address_line' => 'Jl. Checkout No. 1',
            'is_primary' => true,
        ]);

        $response = $this
            ->withSession([
                'checkout' => [
                    'source' => 'cart_selected',
                    'cart_ids' => [$cart->id],
                ],
            ])
            ->postJson(route('frontend.checkout.manual-payment'), [
                'items' => [
                    [
                        'id' => $productVariant->product_id,
                        'productVariantId' => $productVariant->id,
                        'companyId' => $productVariant->product->company_id,
                        'name' => 'Produk Checkout',
                        'variant' => 'Ukuran: M',
                        'price' => 125000,
                        'qty' => 1,
                    ],
                ],
                'company_id' => $productVariant->product->company_id,
                'shipping_cost' => 10000,
                'shipping_label' => 'JNE REG',
                'address_id' => $address->id,
            ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $transaction = Transaction::query()->where('payment_type', 'manual_transfer')->first();
        $this->assertNotNull($transaction);
        $this->assertSame(Transaction::SOURCE_CHECKOUT, $transaction->source);
        $this->assertSame('menunggu_verifikasi', $transaction->status);
        $this->assertSame('Customer Checkout', $transaction->shipping_recipient_name);
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertSame(12, (int) $productVariant->fresh()->stock);
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

    private function makeProductVariant(array $overrides = []): ProductVariant
    {
        $product = Product::create([
            'name' => $overrides['product_name'] ?? 'Produk Manual Test',
            'slug' => 'produk-manual-test-' . uniqid(),
            'status' => 'active',
        ]);
        $variant = Variant::create([
            'name' => 'Ukuran',
            'value' => $overrides['variant_value'] ?? 'M',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => $overrides['sku'] ?? 'PMT-M',
            'price' => $overrides['price'] ?? 125000,
            'stock' => $overrides['stock'] ?? 12,
        ]);
    }
}
