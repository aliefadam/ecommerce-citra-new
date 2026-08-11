<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_queries_work_on_sqlite_and_are_scoped_to_active_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstCompany = Company::query()->where('slug', 'boq')->firstOrFail();
        $secondCompany = Company::create([
            'name' => 'Perusahaan Dashboard Kedua',
            'slug' => 'dashboard-company-two',
            'invoice_prefix' => 'DCT',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->makeTransaction($firstCompany, 'DASH-ONE', 125_000);
        $this->makeTransaction($secondCompany, 'DASH-TWO', 900_000);

        $response = $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $firstCompany->id])
            ->get(route('pages.index', ['period' => 'all']));

        $response->assertOk()
            ->assertViewHas('totalOrders', 1)
            ->assertViewHas('totalRevenue', 125_000)
            ->assertViewHas('revenueByMonth', fn ($values) => (int) $values->sum() === 125_000)
            ->assertViewHas('recentTransactions', fn ($values) => $values->count() === 1
                && $values->first()->order_id === 'DASH-ONE');
    }

    private function makeTransaction(Company $company, string $orderId, int $amount): Transaction
    {
        return Transaction::create([
            'company_id' => $company->id,
            'invoice_no' => 'INV-'.$orderId,
            'order_id' => $orderId,
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA',
            'payment_status' => 'paid',
            'status' => 'paid',
            'subtotal_amount' => $amount,
            'shipping_cost' => 0,
            'grand_total' => $amount,
        ]);
    }
}
