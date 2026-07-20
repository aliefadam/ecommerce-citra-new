<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use App\Models\UserTaxProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerTaxInvoiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_checkout_can_request_tax_invoice_and_save_profile(): void
    {
        Mail::fake();
        $customer = User::factory()->create();
        $this->actingAs($customer);

        $companyId = \App\Models\Company::query()->value('id');

        $response = $this->postJson(route('frontend.checkout.manual-payment'), [
            'items' => [
                [
                    'id' => 1,
                    'productVariantId' => null,
                    'companyId' => $companyId,
                    'name' => 'Produk Pajak',
                    'variant' => 'M10',
                    'price' => 100000,
                    'qty' => 1,
                ],
            ],
            'company_id' => $companyId,
            'shipping_cost' => 0,
            'shipping_label' => 'Ambil sendiri',
            'tax_invoice' => [
                'requested' => true,
                'taxpayer_name' => 'PT Citra Pajak',
                'taxpayer_number' => '12.345.678.9-012.345',
                'taxpayer_address' => 'Jl. NPWP No. 10',
                'taxpayer_email' => 'tax@example.test',
                'customer_note' => 'Butuh bulan ini',
                'save_profile' => true,
                'set_default_profile' => true,
            ],
        ]);

        $response->assertOk()->assertJson(['ok' => true]);
        $transaction = Transaction::query()->firstOrFail();
        $taxInvoice = $transaction->taxInvoice()->first();

        $this->assertNotNull($taxInvoice);
        $this->assertSame(100000, (int) $transaction->subtotal_amount);
        $this->assertSame(100000, (int) $transaction->taxable_amount);
        $this->assertSame(11000, (int) $transaction->tax_amount);
        $this->assertSame(111000, (int) $transaction->grand_total);
        $this->assertSame(TransactionTaxInvoice::STATUS_REQUESTED, $taxInvoice->status);
        $this->assertSame('123456789012345', $taxInvoice->taxpayer_number);
        $this->assertSame('Butuh bulan ini', $taxInvoice->customer_note);
        $this->assertDatabaseHas('user_tax_profiles', [
            'user_id' => $customer->id,
            'taxpayer_number' => '123456789012345',
            'is_default' => true,
        ]);
    }

    public function test_customer_can_request_tax_invoice_from_existing_order_using_saved_profile(): void
    {
        $customer = User::factory()->create();
        $profile = UserTaxProfile::create([
            'user_id' => $customer->id,
            'taxpayer_name' => 'PT Profil Pajak',
            'taxpayer_number' => '1234567890123456',
            'taxpayer_address' => 'Jl. Profil No. 1',
            'taxpayer_email' => 'profil@example.test',
            'is_default' => true,
        ]);
        $transaction = Transaction::create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-PROFILE-TAX',
            'order_id' => 'ORD-PROFILE-TAX',
            'status' => 'paid',
            'subtotal_amount' => 100000,
            'taxable_amount' => 100000,
            'tax_amount' => 11000,
            'shipping_cost' => 0,
            'grand_total' => 111000,
        ]);

        $response = $this
            ->actingAs($customer)
            ->from(route('frontend.profil', ['tab' => 'pesanan']))
            ->post(route('frontend.profil.orders.tax-invoice.store', $transaction), [
                'profile_id' => $profile->id,
                'taxpayer_name' => 'PT Profil Pajak',
                'taxpayer_number' => '1234567890123456',
                'taxpayer_address' => 'Jl. Profil No. 1',
                'taxpayer_email' => 'profil@example.test',
            ]);

        $response->assertRedirect(route('frontend.profil', ['tab' => 'pesanan']));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('transaction_tax_invoices', [
            'transaction_id' => $transaction->id,
            'requested_by_user_id' => $customer->id,
            'status' => TransactionTaxInvoice::STATUS_REQUESTED,
            'taxpayer_name' => 'PT Profil Pajak',
            'taxpayer_number' => '1234567890123456',
        ]);
        $transaction->refresh();
        $this->assertSame(100000, (int) $transaction->subtotal_amount);
        $this->assertSame(100000, (int) $transaction->taxable_amount);
        $this->assertSame(11000, (int) $transaction->tax_amount);
        $this->assertSame(111000, (int) $transaction->grand_total);
    }

    public function test_customer_cannot_request_tax_invoice_for_other_users_order(): void
    {
        $customer = User::factory()->create();
        $otherCustomer = User::factory()->create();
        $transaction = Transaction::create([
            'user_id' => $otherCustomer->id,
            'invoice_no' => 'INV-OTHER-TAX',
            'order_id' => 'ORD-OTHER-TAX',
            'status' => 'paid',
            'subtotal_amount' => 100000,
            'shipping_cost' => 0,
            'grand_total' => 111000,
        ]);

        $response = $this->actingAs($customer)
            ->post(route('frontend.profil.orders.tax-invoice.store', $transaction), [
                'taxpayer_name' => 'PT Bukan Pemilik',
                'taxpayer_number' => '123456789012345',
                'taxpayer_address' => 'Jl. Salah',
                'taxpayer_email' => 'wrong@example.test',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('transaction_tax_invoices', 0);
    }

    public function test_checkout_and_profile_render_customer_tax_invoice_controls(): void
    {
        $customer = User::factory()->create();
        UserTaxProfile::create([
            'user_id' => $customer->id,
            'taxpayer_name' => 'PT Render Pajak',
            'taxpayer_number' => '123456789012345',
            'taxpayer_address' => 'Jl. Render No. 1',
            'taxpayer_email' => 'render@example.test',
            'is_default' => true,
        ]);
        Transaction::create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-RENDER-TAX',
            'order_id' => 'ORD-RENDER-TAX',
            'status' => 'paid',
            'subtotal_amount' => 100000,
            'shipping_cost' => 0,
            'grand_total' => 111000,
        ]);

        $this->actingAs($customer)
            ->get(route('frontend.checkout'))
            ->assertOk()
            ->assertSee('Saya membutuhkan faktur pajak', false)
            ->assertSee('PT Render Pajak', false);

        $this->actingAs($customer)
            ->get(route('frontend.profil', ['tab' => 'pesanan']))
            ->assertOk()
            ->assertSee('Minta Faktur Pajak', false)
            ->assertSee('tax_invoice_request_url', false);
    }
}
