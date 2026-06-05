<?php

namespace Tests\Feature;

use App\Mail\TaxInvoiceAvailable;
use App\Models\AdminRole;
use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class AdminTaxInvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_tax_invoice_queue_with_masked_npwp(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice();

        $response = $this->actingAs($admin)->get(route('tax-invoices.index'));

        $response->assertOk();
        $response->assertSee($taxInvoice->transaction->invoice_no, false);
        $response->assertSee('PT Admin Pajak', false);
        $response->assertSee('1234*******2345', false);
        $response->assertDontSee('123456789012345', false);
    }

    public function test_tax_invoice_detail_respects_sensitive_permission(): void
    {
        $taxInvoice = $this->makeTaxInvoice();
        $limitedAdmin = $this->makeRoleUser(['tax_invoices.index', 'tax_invoices.show']);
        $sensitiveAdmin = $this->makeRoleUser(['tax_invoices.index', 'tax_invoices.show', 'tax_invoices.view_sensitive']);

        $this->actingAs($limitedAdmin)
            ->get(route('tax-invoices.show', $taxInvoice))
            ->assertOk()
            ->assertSee('1234*******2345', false)
            ->assertDontSee('123456789012345', false);

        $this->actingAs($sensitiveAdmin)
            ->get(route('tax-invoices.show', $taxInvoice))
            ->assertOk()
            ->assertSee('123456789012345', false);
    }

    public function test_admin_tax_invoice_queue_filters_by_status_and_keyword(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $requested = $this->makeTaxInvoice([
            'taxpayer_name' => 'PT Filter Requested',
            'taxpayer_number' => '1111222233334444',
        ]);
        $processing = $this->makeTaxInvoice([
            'status' => TransactionTaxInvoice::STATUS_PROCESSING,
            'taxpayer_name' => 'PT Filter Processing',
            'taxpayer_number' => '9999888877776666',
        ]);

        $this->actingAs($admin)
            ->get(route('tax-invoices.index', [
                'status' => TransactionTaxInvoice::STATUS_PROCESSING,
                'q' => 'Processing',
            ]))
            ->assertOk()
            ->assertSee($processing->transaction->invoice_no, false)
            ->assertSee('PT Filter Processing', false)
            ->assertDontSee('PT Filter Requested', false);
    }

    public function test_admin_can_mark_tax_invoice_processing_and_reject_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice();

        $this->actingAs($admin)
            ->patch(route('tax-invoices.process', $taxInvoice))
            ->assertRedirect();

        $this->assertSame(TransactionTaxInvoice::STATUS_PROCESSING, $taxInvoice->fresh()->status);
        $this->assertNotNull($taxInvoice->fresh()->processing_at);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $taxInvoice->transaction_id,
            'from_status' => TransactionTaxInvoice::STATUS_REQUESTED,
            'to_status' => TransactionTaxInvoice::STATUS_PROCESSING,
            'type' => 'tax_invoice_status',
        ]);

        $this->actingAs($admin)
            ->patch(route('tax-invoices.reject', $taxInvoice), [
                'rejected_reason' => 'NPWP tidak sesuai dengan nama wajib pajak.',
                'admin_note' => 'Minta customer revisi.',
            ])
            ->assertRedirect();

        $fresh = $taxInvoice->fresh();
        $this->assertSame(TransactionTaxInvoice::STATUS_REJECTED, $fresh->status);
        $this->assertSame('NPWP tidak sesuai dengan nama wajib pajak.', $fresh->rejected_reason);
        $this->assertSame('Minta customer revisi.', $fresh->admin_note);
        $this->assertNotNull($fresh->rejected_at);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $taxInvoice->transaction_id,
            'from_status' => TransactionTaxInvoice::STATUS_PROCESSING,
            'to_status' => TransactionTaxInvoice::STATUS_REJECTED,
            'type' => 'tax_invoice_status',
        ]);
    }

    public function test_admin_can_upload_tax_invoice_pdf_and_send_email(): void
    {
        Storage::fake('local');
        Mail::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice();

        $this->actingAs($admin)
            ->post(route('tax-invoices.upload', $taxInvoice), [
                'tax_invoice_file' => UploadedFile::fake()->create('faktur-pajak.pdf', 40, 'application/pdf'),
                'tax_invoice_number' => '010.001-26.12345678',
                'tax_invoice_date' => '2026-06-06',
                'admin_note' => 'Sudah diterbitkan finance.',
                'send_email' => '1',
            ])
            ->assertRedirect();

        $fresh = $taxInvoice->fresh();

        $this->assertSame(TransactionTaxInvoice::STATUS_SENT, $fresh->status);
        $this->assertSame('010.001-26.12345678', $fresh->tax_invoice_number);
        $this->assertNotNull($fresh->tax_invoice_file_path);
        $this->assertNotNull($fresh->issued_at);
        $this->assertNotNull($fresh->sent_at);
        Storage::disk('local')->assertExists($fresh->tax_invoice_file_path);

        Mail::assertSent(TaxInvoiceAvailable::class, function (TaxInvoiceAvailable $mail) use ($fresh) {
            return (int) $mail->taxInvoice->id === (int) $fresh->id;
        });

        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $fresh->transaction_id,
            'to_status' => TransactionTaxInvoice::STATUS_ISSUED,
            'type' => 'tax_invoice_status',
        ]);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $fresh->transaction_id,
            'to_status' => TransactionTaxInvoice::STATUS_SENT,
            'type' => 'tax_invoice_status',
        ]);
    }

    public function test_admin_and_owner_customer_can_download_tax_invoice_pdf(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice([
            'status' => TransactionTaxInvoice::STATUS_ISSUED,
            'tax_invoice_file_path' => 'tax-invoices/testing/faktur.pdf',
            'issued_at' => now(),
        ]);
        Storage::disk('local')->put($taxInvoice->tax_invoice_file_path, '%PDF-1.4 testing');

        $this->actingAs($admin)
            ->get(route('tax-invoices.download', $taxInvoice))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($taxInvoice->transaction->user)
            ->get(route('frontend.profil.orders.tax-invoice.download', $taxInvoice->transaction))
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->assertNotNull($taxInvoice->fresh()->last_downloaded_at);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $taxInvoice->transaction_id,
            'type' => 'tax_invoice_download',
            'note' => 'File faktur pajak diunduh oleh admin.',
        ]);
        $this->assertDatabaseHas('transaction_status_histories', [
            'transaction_id' => $taxInvoice->transaction_id,
            'type' => 'tax_invoice_download',
            'note' => 'File faktur pajak diunduh oleh customer.',
        ]);
    }

    public function test_other_customer_cannot_download_tax_invoice_pdf(): void
    {
        Storage::fake('local');

        $otherCustomer = User::factory()->create();
        $taxInvoice = $this->makeTaxInvoice([
            'status' => TransactionTaxInvoice::STATUS_ISSUED,
            'tax_invoice_file_path' => 'tax-invoices/testing/private.pdf',
            'issued_at' => now(),
        ]);
        Storage::disk('local')->put($taxInvoice->tax_invoice_file_path, '%PDF-1.4 private');

        $this->actingAs($otherCustomer)
            ->get(route('frontend.profil.orders.tax-invoice.download', $taxInvoice->transaction))
            ->assertForbidden();
    }

    public function test_admin_upload_rejects_non_pdf_file(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice();

        $this->actingAs($admin)
            ->from(route('tax-invoices.show', $taxInvoice))
            ->post(route('tax-invoices.upload', $taxInvoice), [
                'tax_invoice_file' => UploadedFile::fake()->create('faktur-pajak.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('tax-invoices.show', $taxInvoice))
            ->assertSessionHasErrors('tax_invoice_file');

        $this->assertNull($taxInvoice->fresh()->tax_invoice_file_path);
        Storage::disk('local')->assertMissing('tax-invoices/'.$taxInvoice->transaction_id);
    }

    public function test_failed_tax_invoice_email_is_recorded_without_leaking_sensitive_data(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $taxInvoice = $this->makeTaxInvoice([
            'status' => TransactionTaxInvoice::STATUS_ISSUED,
            'tax_invoice_file_path' => 'tax-invoices/testing/email-fail.pdf',
            'issued_at' => now(),
        ]);
        Storage::disk('local')->put($taxInvoice->tax_invoice_file_path, '%PDF-1.4 email fail');

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('SMTP failed for 123456789012345'));

        $this->actingAs($admin)
            ->post(route('tax-invoices.send', $taxInvoice))
            ->assertRedirect()
            ->assertSessionHasErrors('tax_invoice_email');

        $fresh = $taxInvoice->fresh();
        $this->assertSame(TransactionTaxInvoice::STATUS_ISSUED, $fresh->status);
        $this->assertNotNull($fresh->email_failed_at);
        $this->assertStringNotContainsString('123456789012345', (string) $fresh->email_failure_reason);

        $history = $taxInvoice->transaction->statusHistories()
            ->where('type', 'tax_invoice_email')
            ->latest('id')
            ->firstOrFail();

        $this->assertStringNotContainsString('123456789012345', (string) $history->note);
        $this->assertSame(TransactionTaxInvoice::STATUS_ISSUED, $history->from_status);
        $this->assertSame(TransactionTaxInvoice::STATUS_ISSUED, $history->to_status);
    }

    private function makeTaxInvoice(array $overrides = []): TransactionTaxInvoice
    {
        $customer = User::factory()->create([
            'name' => 'Customer Faktur',
            'email' => 'customer-faktur-'.uniqid().'@example.test',
        ]);

        $transaction = Transaction::create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-TAX-ADMIN-'.uniqid(),
            'order_id' => 'ORD-TAX-ADMIN-'.uniqid(),
            'status' => 'paid',
            'subtotal_amount' => 100000,
            'taxable_amount' => 100000,
            'tax_amount' => 11000,
            'shipping_cost' => 0,
            'grand_total' => 111000,
        ]);

        return TransactionTaxInvoice::create(array_merge([
            'transaction_id' => $transaction->id,
            'requested_by_user_id' => $customer->id,
            'status' => TransactionTaxInvoice::STATUS_REQUESTED,
            'taxpayer_name' => 'PT Admin Pajak',
            'taxpayer_number' => '123456789012345',
            'taxpayer_address' => 'Jl. Admin Pajak No. 1',
            'taxpayer_email' => 'tax-admin@example.test',
            'requested_at' => now(),
        ], $overrides));
    }

    private function makeRoleUser(array $permissions): User
    {
        $role = AdminRole::create([
            'name' => 'Tax Role '.uniqid(),
            'slug' => 'tax-role-'.uniqid(),
            'permissions' => $permissions,
            'is_system' => false,
        ]);

        return User::factory()->create([
            'role' => 'staff',
            'admin_role_id' => $role->id,
        ]);
    }
}
