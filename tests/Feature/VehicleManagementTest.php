<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleManagementTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::query()->where('slug', 'boq')->firstOrFail();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin)->withSession(['admin_active_company_id' => $this->company->id]);
    }

    public function test_vehicle_master_is_company_scoped_and_can_be_managed(): void
    {
        $otherCompany = Company::query()->create([
            'name' => 'Perusahaan Lain',
            'slug' => 'perusahaan-lain-kendaraan',
            'invoice_prefix' => 'PLK',
            'is_active' => true,
            'sort_order' => 99,
        ]);
        $otherVehicle = Vehicle::query()->create([
            'company_id' => $otherCompany->id,
            'name' => 'Truk Rahasia',
            'type' => 'truk',
            'rate_per_kg' => 9000,
            'is_active' => true,
        ]);

        $this->get(route('vehicles.index'))
            ->assertOk()
            ->assertSee('Motor Kurir')
            ->assertSee('Van Pengiriman')
            ->assertDontSee('Truk Rahasia');

        $this->post(route('vehicles.store'), [
            'name' => 'Pickup Kota',
            'type' => 'pickup',
            'plate_number' => 'B 1234 PKP',
            'capacity_kg' => 800,
            'rate_per_kg' => 3500,
            'distance_block_km' => 5,
            'rate_per_distance_block' => 5000,
            'sort_order' => 30,
            'is_active' => 1,
        ])->assertRedirect(route('vehicles.index'));

        $this->assertDatabaseHas('vehicles', [
            'company_id' => $this->company->id,
            'name' => 'Pickup Kota',
            'rate_per_kg' => 3500,
        ]);

        $this->get(route('vehicles.edit', $otherVehicle))->assertNotFound();
        $this->put(route('vehicles.update', $otherVehicle), [
            'name' => 'Disusupi',
            'type' => 'truk',
            'rate_per_kg' => 1,
            'distance_block_km' => 5,
            'rate_per_distance_block' => 5000,
        ])->assertNotFound();
    }

    public function test_store_courier_cost_is_calculated_server_side_from_vehicle_rate(): void
    {
        $vehicle = Vehicle::query()
            ->where('company_id', $this->company->id)
            ->where('type', 'motor')
            ->firstOrFail();
        $vehicle->update([
            'rate_per_kg' => 5000,
            'distance_block_km' => 5,
            'rate_per_distance_block' => 5000,
            'capacity_kg' => 20,
            'is_active' => true,
        ]);
        $transaction = $this->manualTransaction();

        $this->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_type' => 'kurir_toko',
            'shipping_vehicle_id' => $vehicle->id,
            'shipping_weight_kg' => 1.2,
            'shipping_distance_km' => 8,
            'shipping_recipient_name' => 'Penerima Toko',
            'shipping_phone' => '081234567890',
            'shipping_address_line' => 'Jl. Tujuan No. 10',
            'shipping_cost' => 1,
            'shipping_courier_name' => 'Kurir Palsu',
        ])->assertRedirect();

        $fresh = $transaction->fresh();
        $this->assertSame($vehicle->id, $fresh->shipping_vehicle_id);
        $this->assertSame(1200, $fresh->shipping_weight_grams);
        $this->assertSame('8.00', $fresh->shipping_distance_km);
        $this->assertSame(5000, $fresh->shipping_rate_per_kg);
        $this->assertSame('5.00', $fresh->shipping_distance_block_km);
        $this->assertSame(5000, $fresh->shipping_distance_rate);
        $this->assertSame(10000, $fresh->shipping_weight_cost);
        $this->assertSame(10000, $fresh->shipping_distance_cost);
        $this->assertSame(20000, (int) $fresh->shipping_cost);
        $this->assertSame(120000, (int) $fresh->grand_total);
        $this->assertSame('Motor Kurir', $fresh->shipping_courier_name);
        $this->assertSame('Motor', $fresh->shipping_service);
        $this->assertSame('Motor Kurir · Motor', $fresh->shipping_label);
    }

    public function test_store_courier_rejects_weight_over_vehicle_capacity(): void
    {
        $vehicle = Vehicle::query()
            ->where('company_id', $this->company->id)
            ->where('type', 'motor')
            ->firstOrFail();
        $vehicle->update(['capacity_kg' => 20, 'is_active' => true]);
        $transaction = $this->manualTransaction();

        $this->from(route('transactions.show', $transaction))
            ->patch(route('transactions.manual-shipping.update', $transaction), [
                'shipping_type' => 'kurir_toko',
                'shipping_vehicle_id' => $vehicle->id,
                'shipping_weight_kg' => 20.001,
                'shipping_distance_km' => 5,
                'shipping_recipient_name' => 'Penerima Toko',
                'shipping_phone' => '081234567890',
                'shipping_address_line' => 'Jl. Tujuan No. 10',
            ])
            ->assertRedirect(route('transactions.show', $transaction))
            ->assertSessionHasErrors('shipping_weight_kg');

        $this->assertNull($transaction->fresh()->shipping_vehicle_id);
    }

    private function manualTransaction(): Transaction
    {
        return Transaction::query()->create([
            'company_id' => $this->company->id,
            'source' => Transaction::SOURCE_MANUAL,
            'created_by_admin_id' => $this->admin->id,
            'manual_customer_name' => 'Customer Manual',
            'invoice_no' => 'INV-VEHICLE-'.uniqid(),
            'order_id' => 'ORD-VEHICLE-'.uniqid(),
            'payment_type' => 'manual_admin',
            'payment_method' => 'Manual Admin',
            'status' => 'pending',
            'subtotal_amount' => 100000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'shipping_cost' => 0,
            'grand_total' => 100000,
            'shipping_type' => 'belum_ditentukan',
            'shipping_label' => 'Belum ditentukan',
        ]);
    }
}
