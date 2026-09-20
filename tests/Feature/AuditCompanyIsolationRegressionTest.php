<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\AdminRole;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditCompanyIsolationRegressionTest extends TestCase
{
    use RefreshDatabase;

    private Company $activeCompany;

    private Company $otherCompany;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCompany = Company::query()->where('slug', 'boq')->firstOrFail();
        $this->otherCompany = Company::query()->create([
            'name' => 'PT Audit Perusahaan Lain',
            'slug' => 'audit-perusahaan-lain',
            'invoice_prefix' => 'APL',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->admin)
            ->withSession(['admin_active_company_id' => $this->activeCompany->id]);
    }

    public function test_owner_report_only_contains_active_company_data(): void
    {
        $activeTransaction = $this->makeTransaction($this->activeCompany, 'ACTIVE');
        $otherTransaction = $this->makeTransaction($this->otherCompany, 'OTHER');

        $this->get(route('reports.owner'))
            ->assertOk()
            ->assertSee($activeTransaction->invoice_no, false)
            ->assertDontSee($otherTransaction->invoice_no, false);
    }

    public function test_admin_cannot_open_invoice_from_another_active_company(): void
    {
        $otherTransaction = $this->makeTransaction($this->otherCompany, 'INVOICE-GUARDED');

        $this->get(route('invoice.show', $otherTransaction))->assertNotFound();
    }

    public function test_every_operational_report_follows_the_active_company(): void
    {
        $activeTransaction = $this->makeTransaction($this->activeCompany, 'SCOPE-ACTIVE');
        $otherTransaction = $this->makeTransaction($this->otherCompany, 'SCOPE-OTHER');
        $activeProduct = $this->makeProduct($this->activeCompany, 'Produk Scope Aktif');
        $otherProduct = $this->makeProduct($this->otherCompany, 'Produk Scope Lain');

        TransactionDetail::query()->create([
            'transaction_id' => $activeTransaction->id,
            'product_id' => $activeProduct->id,
            'product_variant_id' => $activeProduct->productVariants()->value('id'),
            'product_name' => $activeProduct->name,
            'price' => 100_000,
            'quantity' => 1,
            'subtotal' => 100_000,
        ]);
        TransactionDetail::query()->create([
            'transaction_id' => $otherTransaction->id,
            'product_id' => $otherProduct->id,
            'product_variant_id' => $otherProduct->productVariants()->value('id'),
            'product_name' => $otherProduct->name,
            'price' => 100_000,
            'quantity' => 1,
            'subtotal' => 100_000,
        ]);

        foreach ([[$this->activeCompany, 'COUPON-AKTIF'], [$this->otherCompany, 'COUPON-LAIN']] as [$company, $code]) {
            Coupon::query()->create([
                'company_id' => $company->id,
                'code' => $code,
                'name' => $code,
                'type' => 'fixed',
                'value' => 10_000,
                'is_active' => true,
            ]);
        }

        ReturnRequest::query()->create([
            'transaction_id' => $activeTransaction->id,
            'user_id' => $activeTransaction->user_id,
            'request_no' => 'RET-SCOPE-AKTIF',
            'type' => 'refund',
            'status' => 'pending',
            'refund_amount' => 10_000,
            'reason' => 'Audit',
        ]);
        ReturnRequest::query()->create([
            'transaction_id' => $otherTransaction->id,
            'user_id' => $otherTransaction->user_id,
            'request_no' => 'RET-SCOPE-LAIN',
            'type' => 'refund',
            'status' => 'pending',
            'refund_amount' => 10_000,
            'reason' => 'Audit',
        ]);

        foreach (['reports.owner', 'reports.sales', 'reports.payments'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('INV-AUDIT-SCOPE-ACTIVE', false)
                ->assertDontSee('INV-AUDIT-SCOPE-OTHER', false);
        }

        $csv = $this->get(route('reports.sales', ['export' => 'csv']))
            ->assertOk()
            ->streamedContent();
        $this->assertStringContainsString('INV-AUDIT-SCOPE-ACTIVE', $csv);
        $this->assertStringNotContainsString('INV-AUDIT-SCOPE-OTHER', $csv);

        foreach (['reports.stock', 'reports.products'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('Produk Scope Aktif', false)
                ->assertDontSee('Produk Scope Lain', false);
        }

        $this->get(route('reports.customers'))
            ->assertOk()
            ->assertSee($activeTransaction->user->name, false)
            ->assertDontSee($otherTransaction->user->name, false);

        $this->get(route('reports.promos'))
            ->assertOk()
            ->assertSee('COUPON-AKTIF', false)
            ->assertDontSee('COUPON-LAIN', false);

        $this->get(route('reports.returns'))
            ->assertOk()
            ->assertSee('RET-SCOPE-AKTIF', false)
            ->assertDontSee('RET-SCOPE-LAIN', false);
    }

    public function test_consolidated_report_requires_dedicated_permission_and_breaks_down_companies(): void
    {
        $this->makeTransaction($this->activeCompany, 'CONSOLIDATED-ACTIVE');
        $this->makeTransaction($this->otherCompany, 'CONSOLIDATED-OTHER');

        $role = AdminRole::query()->create([
            'name' => 'Audit Report Staff',
            'slug' => 'audit-report-staff',
            'permissions' => ['reports.index', 'reports.owner'],
        ]);
        $staff = User::factory()->create([
            'role' => 'staff',
            'admin_role_id' => $role->id,
        ]);

        $this->actingAs($staff)
            ->withSession(['admin_active_company_id' => $this->activeCompany->id])
            ->get(route('reports.consolidated'))
            ->assertRedirect(route('pages.index'));

        $role->update(['permissions' => ['reports.index', 'reports.owner', 'reports.consolidated']]);
        $staff->unsetRelation('adminRole')->unsetRelation('companyAssignments');

        $this->actingAs($staff)
            ->withSession(['admin_active_company_id' => $this->activeCompany->id])
            ->get(route('reports.consolidated'))
            ->assertOk()
            ->assertSee($this->activeCompany->name, false)
            ->assertSee($this->otherCompany->name, false)
            ->assertSee('Rp 200.000', false);
    }

    private function makeTransaction(Company $company, string $suffix): Transaction
    {
        return Transaction::query()->create([
            'company_id' => $company->id,
            'user_id' => User::factory()->create()->id,
            'invoice_no' => 'INV-AUDIT-'.$suffix,
            'order_id' => 'ORD-AUDIT-'.$suffix,
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA',
            'status' => 'paid',
            'subtotal_amount' => 100_000,
            'shipping_cost' => 0,
            'grand_total' => 100_000,
        ]);
    }

    private function makeProduct(Company $company, string $name): Product
    {
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => $name,
            'slug' => str($name)->slug().'-'.str()->random(6),
            'status' => 'active',
        ]);
        $variant = Variant::query()->create([
            'name' => 'Scope',
            'value' => str()->random(8),
        ]);
        ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'SCOPE-'.str()->upper(str()->random(8)),
            'price' => 100_000,
            'stock' => 5,
        ]);

        return $product;
    }
}
