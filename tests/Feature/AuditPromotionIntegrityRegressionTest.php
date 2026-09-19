<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditPromotionIntegrityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_flash_sale_rejects_variant_from_another_company(): void
    {
        [$activeCompany, $otherCompany] = $this->companies();
        $otherVariant = $this->makeVariant($otherCompany);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $activeCompany->id])
            ->from(route('flash-sales.create'))
            ->post(route('flash-sales.store'), [
                'name' => 'Flash Sale Lintas Perusahaan',
                'start_at' => now()->addHour()->format('Y-m-d H:i:s'),
                'end_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'status' => 'active',
                'items' => [[
                    'product_variant_id' => $otherVariant->id,
                    'discount_price' => 10_000,
                    'quota' => 5,
                    'is_active' => 1,
                ]],
            ])
            ->assertRedirect(route('flash-sales.create'))
            ->assertSessionHasErrors('items.0.product_variant_id');

        $this->assertDatabaseCount('flash_sales', 0);
        $this->assertDatabaseCount('flash_sale_items', 0);
    }

    public function test_percent_coupon_above_one_hundred_is_rejected(): void
    {
        [$activeCompany] = $this->companies();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $activeCompany->id])
            ->post(route('coupons.store'), [
                'code' => 'OVER100',
                'name' => 'Diskon Tidak Valid',
                'type' => 'percent',
                'value' => 101,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('value');

        $this->assertDatabaseMissing('coupons', ['code' => 'OVER100']);
    }

    public function test_flash_sale_update_rejects_variant_from_another_company_without_losing_existing_items(): void
    {
        [$activeCompany, $otherCompany] = $this->companies();
        $activeVariant = $this->makeVariant($activeCompany);
        $otherVariant = $this->makeVariant($otherCompany);
        $flashSale = FlashSale::query()->create([
            'company_id' => $activeCompany->id,
            'name' => 'Flash Sale Aktif',
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
            'status' => 'active',
        ]);
        $existingItem = $flashSale->items()->create([
            'product_variant_id' => $activeVariant->id,
            'discount_price' => 80_000,
            'quota' => 5,
            'sold' => 0,
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $activeCompany->id])
            ->from(route('flash-sales.edit', $flashSale))
            ->put(route('flash-sales.update', $flashSale), [
                'name' => 'Flash Sale Disusupi',
                'start_at' => now()->addHour()->format('Y-m-d H:i:s'),
                'end_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'status' => 'active',
                'items' => [[
                    'product_variant_id' => $otherVariant->id,
                    'discount_price' => 10_000,
                    'quota' => 5,
                    'is_active' => 1,
                ]],
            ])
            ->assertRedirect(route('flash-sales.edit', $flashSale))
            ->assertSessionHasErrors('items.0.product_variant_id');

        $this->assertDatabaseHas('flash_sale_items', [
            'id' => $existingItem->id,
            'flash_sale_id' => $flashSale->id,
            'product_variant_id' => $activeVariant->id,
        ]);
        $this->assertSame('Flash Sale Aktif', $flashSale->fresh()->name);
    }

    public function test_different_companies_can_use_the_same_coupon_code(): void
    {
        [$activeCompany, $otherCompany] = $this->companies();
        Coupon::query()->create([
            'company_id' => $activeCompany->id,
            'code' => 'WELCOME10',
            'name' => 'Welcome Perusahaan Aktif',
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['admin_active_company_id' => $otherCompany->id])
            ->post(route('coupons.store'), [
                'code' => 'welcome10',
                'name' => 'Welcome Perusahaan Lain',
                'type' => 'percent',
                'value' => 10,
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Coupon::query()->where('code', 'WELCOME10')->count());
    }

    private function companies(): array
    {
        $activeCompany = Company::query()->where('slug', 'boq')->firstOrFail();
        $otherCompany = Company::query()->create([
            'name' => 'PT Promo Audit Lain',
            'slug' => 'promo-audit-lain',
            'invoice_prefix' => 'PAL',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        return [$activeCompany, $otherCompany];
    }

    private function makeVariant(Company $company): ProductVariant
    {
        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Produk Promo '.$company->id,
            'slug' => 'produk-promo-'.$company->id,
            'status' => 'active',
        ]);
        $variant = Variant::query()->create([
            'name' => 'Promo Audit '.$company->id,
            'value' => 'Standar',
        ]);

        return ProductVariant::query()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'PROMO-AUDIT-'.$company->id,
            'price' => 100_000,
            'stock' => 10,
            'weight_grams' => 1_000,
        ]);
    }
}
