<?php

namespace Tests\Feature;

use App\Models\AdminRole;
use App\Models\Transaction;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use App\Models\UserTaxProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxInvoiceDataModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_tax_profile_normalizes_and_masks_npwp(): void
    {
        $user = User::factory()->create();

        $profile = UserTaxProfile::create([
            'user_id' => $user->id,
            'taxpayer_name' => 'PT Citra Test',
            'taxpayer_number' => '12.345.678.9-012.345',
            'taxpayer_address' => 'Jl. Pajak No. 1',
            'taxpayer_email' => 'tax@example.test',
            'is_default' => true,
        ]);

        $this->assertSame('123456789012345', $profile->taxpayer_number);
        $this->assertSame('1234*******2345', $profile->masked_taxpayer_number);
        $this->assertTrue($user->taxProfiles()->whereKey($profile)->exists());
    }

    public function test_transaction_tax_invoice_relations_and_statuses(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = Transaction::create([
            'user_id' => $customer->id,
            'invoice_no' => 'INV-TAX-1',
            'order_id' => 'ORD-TAX-1',
            'status' => 'paid',
            'subtotal_amount' => 100000,
            'shipping_cost' => 0,
            'grand_total' => 111000,
        ]);

        $taxInvoice = TransactionTaxInvoice::create([
            'transaction_id' => $transaction->id,
            'requested_by_user_id' => $customer->id,
            'uploaded_by_admin_id' => $admin->id,
            'status' => TransactionTaxInvoice::STATUS_REQUESTED,
            'taxpayer_name' => 'PT Citra Test',
            'taxpayer_number' => '1234567890123456',
            'taxpayer_address' => 'Jl. Pajak No. 1',
            'taxpayer_email' => 'tax@example.test',
            'requested_at' => now(),
        ]);

        $this->assertSame(TransactionTaxInvoice::STATUS_REQUESTED, $transaction->taxInvoice->status);
        $this->assertSame($customer->id, $taxInvoice->requestedByUser->id);
        $this->assertSame($admin->id, $taxInvoice->uploadedByAdmin->id);
        $this->assertSame('1234********3456', $taxInvoice->masked_taxpayer_number);
        $this->assertContains(TransactionTaxInvoice::STATUS_SENT, TransactionTaxInvoice::STATUSES);
    }

    public function test_tax_invoice_permissions_are_available_and_seeded_to_transaction_roles(): void
    {
        $permissions = collect(config('admin_permissions.groups.transactions.modules.tax_invoices.permissions'))
            ->pluck('key')
            ->all();

        $this->assertSame([
            'tax_invoices.index',
            'tax_invoices.show',
            'tax_invoices.process',
            'tax_invoices.reject',
            'tax_invoices.upload',
            'tax_invoices.send',
            'tax_invoices.view_sensitive',
        ], $permissions);

        $storeManagerPermissions = AdminRole::where('slug', 'store-manager')->firstOrFail()->permissions;
        $orderStaffPermissions = AdminRole::where('slug', 'order-staff')->firstOrFail()->permissions;

        $this->assertContains('tax_invoices.upload', $storeManagerPermissions);
        $this->assertContains('tax_invoices.view_sensitive', $storeManagerPermissions);
        $this->assertContains('tax_invoices.upload', $orderStaffPermissions);
        $this->assertNotContains('tax_invoices.view_sensitive', $orderStaffPermissions);
    }
}
