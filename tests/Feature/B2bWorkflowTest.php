<?php

namespace Tests\Feature;

use App\Models\B2bInvoice;
use App\Models\Company;
use App\Models\DeliveryNote;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProformaInvoice;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class B2bWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_to_fulfillment_and_invoice_happy_path_keeps_totals_and_stock_consistent(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::query()->where('slug', 'boq')->firstOrFail();
        $variant = $this->makeProductVariant($company, stock: 10, price: 100_000);

        $this->actingAs($admin)->withSession(['admin_active_company_id' => $company->id]);

        $this->post(route('quotations.store'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'PT Pembeli Uji',
            'manual_customer_phone' => '08123456789',
            'manual_customer_email' => 'purchasing@example.test',
            'items' => [[
                'product_variant_id' => $variant->id,
                'qty' => 2,
                'price' => 100_000,
            ]],
            'discount_amount' => 10_000,
            'ppn_rate' => 11,
            'shipping_cost' => 15_000,
            'admin_fee' => 5_000,
            'other_cost' => 2_500,
            'valid_until' => now()->addWeek()->toDateString(),
        ])->assertRedirect();

        $quotation = Quotation::query()->sole();
        $this->assertSame(200_000, $quotation->subtotal_amount);
        $this->assertSame(20_900, $quotation->ppn_amount);
        $this->assertSame(233_400, $quotation->grand_total);

        $this->patch(route('quotations.update-status', $quotation), [
            'to_status' => Quotation::STATUS_ACCEPTED,
        ])->assertRedirect();

        $quotationDetail = $quotation->details()->sole();
        $this->post(route('quotations.convert', $quotation), [
            'items' => [[
                'quotation_detail_id' => $quotationDetail->id,
                'qty' => 2,
            ]],
            'ppn_rate' => 11,
            'shipping_cost' => 15_000,
            'admin_fee' => 5_000,
            'other_cost' => 2_500,
        ])->assertRedirect();

        $salesOrder = SalesOrder::query()->sole();
        $salesOrderDetail = $salesOrder->details()->sole();
        $this->assertSame($company->id, $salesOrder->company_id);
        $this->assertSame(244_500, $salesOrder->grand_total);
        $this->assertSame(Quotation::STATUS_CLOSED, $quotation->fresh()->status);

        $this->post(route('proforma-invoices.store', $salesOrder), [
            'items' => [[
                'sales_order_detail_id' => $salesOrderDetail->id,
                'qty' => 2,
            ]],
            'ppn_rate' => 11,
            'shipping_cost' => 15_000,
            'admin_fee' => 5_000,
            'other_cost' => 2_500,
        ])->assertRedirect();

        $proforma = ProformaInvoice::query()->sole();
        $this->assertSame(244_500, $proforma->grand_total);

        $this->post(route('proforma-invoices.record-payment', $proforma), [
            'amount' => 100_000,
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $proforma->refresh();
        $this->assertSame(ProformaInvoice::STATUS_PARTIALLY_PAID, $proforma->status);
        $this->assertSame(100_000, $proforma->paid_amount);
        $this->assertSame(144_500, $proforma->outstanding_amount);

        $this->post(route('delivery-notes.store', $salesOrder), [
            'recipient_name' => 'Gudang PT Pembeli Uji',
            'shipping_address' => 'Jl. Industri No. 1, Jakarta',
            'courier_name' => 'Kurir Internal',
            'total_packages' => 1,
            'items' => [[
                'sales_order_detail_id' => $salesOrderDetail->id,
                'qty' => 2,
            ]],
        ])->assertRedirect();

        $deliveryNote = DeliveryNote::query()->sole();
        $this->post(route('delivery-notes.ship', $deliveryNote))->assertRedirect();
        $this->assertSame(8, (int) $variant->fresh()->stock);
        $this->assertSame(SalesOrder::STATUS_FULFILLED, $salesOrder->fresh()->status);

        $this->post(route('b2b-invoices.store', $salesOrder), [
            'delivery_note_ids' => [$deliveryNote->id],
            'due_date' => now()->addMonth()->toDateString(),
            'ppn_rate' => 11,
            'shipping_cost' => 15_000,
            'admin_fee' => 5_000,
            'other_cost' => 2_500,
        ])->assertRedirect();

        $invoice = B2bInvoice::query()->sole();
        $this->assertSame(B2bInvoice::SOURCE_SHIPMENT, $invoice->source);
        $this->assertSame(244_500, $invoice->grand_total);
        $this->assertSame(100_000, $invoice->paid_amount);
        $this->assertSame(144_500, $invoice->outstanding_amount);
        $this->assertTrue($invoice->deliveryNotes()->whereKey($deliveryNote->id)->exists());

        $this->post(route('b2b-invoices.record-payment', $invoice), [
            'amount' => 50_000,
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $invoice->refresh();
        $this->assertSame(B2bInvoice::STATUS_PARTIALLY_PAID, $invoice->status);
        $this->assertSame(150_000, $invoice->paid_amount);
        $this->assertSame(94_500, $invoice->outstanding_amount);
    }

    public function test_paid_document_remains_intact_when_sales_order_is_cancelled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $company = Company::query()->where('slug', 'boq')->firstOrFail();
        $variant = $this->makeProductVariant($company);

        $this->actingAs($admin)->withSession(['admin_active_company_id' => $company->id]);
        [$salesOrder, $detail] = $this->makeSalesOrderThroughQuotation($variant);

        $this->post(route('proforma-invoices.store', $salesOrder), [
            'items' => [['sales_order_detail_id' => $detail->id, 'qty' => 1]],
            'ppn_rate' => 0,
        ])->assertRedirect();

        $proforma = ProformaInvoice::query()->sole();
        $this->post(route('proforma-invoices.record-payment', $proforma), [
            'amount' => 25_000,
            'payment_date' => now()->toDateString(),
        ])->assertRedirect();

        $this->post(route('sales-orders.cancel', $salesOrder))->assertRedirect();

        $this->assertSame(SalesOrder::STATUS_CANCELLED, $salesOrder->fresh()->status);
        $this->assertDatabaseHas('proforma_invoices', [
            'id' => $proforma->id,
            'status' => ProformaInvoice::STATUS_PARTIALLY_PAID,
            'paid_amount' => 25_000,
        ]);
        $this->assertDatabaseHas('document_payments', [
            'payable_type' => ProformaInvoice::class,
            'payable_id' => $proforma->id,
            'amount' => 25_000,
        ]);
    }

    public function test_b2b_documents_are_not_accessible_from_another_active_company(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstCompany = Company::query()->where('slug', 'boq')->firstOrFail();
        $secondCompany = Company::create([
            'name' => 'Perusahaan Kedua',
            'slug' => 'company-two',
            'invoice_prefix' => 'TWO',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $variant = $this->makeProductVariant($firstCompany);

        $this->actingAs($admin)->withSession(['admin_active_company_id' => $firstCompany->id]);
        [$salesOrder] = $this->makeSalesOrderThroughQuotation($variant);

        $this->withSession(['admin_active_company_id' => $secondCompany->id]);

        $this->get(route('sales-orders.show', $salesOrder))->assertNotFound();
        $this->get(route('quotations.show', $salesOrder->quotation_id))->assertNotFound();
        $this->get(route('sales-orders.index'))->assertOk()->assertDontSee($salesOrder->sales_order_no, false);
    }

    private function makeSalesOrderThroughQuotation(ProductVariant $variant): array
    {
        $this->post(route('quotations.store'), [
            'customer_mode' => 'manual',
            'manual_customer_name' => 'PT Uji B2B',
            'manual_customer_phone' => '0811111111',
            'items' => [[
                'product_variant_id' => $variant->id,
                'qty' => 1,
                'price' => 100_000,
            ]],
            'valid_until' => now()->addWeek()->toDateString(),
        ])->assertRedirect();

        $quotation = Quotation::query()->latest('id')->firstOrFail();
        $this->patch(route('quotations.update-status', $quotation), [
            'to_status' => Quotation::STATUS_ACCEPTED,
        ])->assertRedirect();

        $quotationDetail = $quotation->details()->sole();
        $this->post(route('quotations.convert', $quotation), [
            'items' => [['quotation_detail_id' => $quotationDetail->id, 'qty' => 1]],
            'ppn_rate' => 0,
        ])->assertRedirect();

        $salesOrder = SalesOrder::query()->latest('id')->firstOrFail();

        return [$salesOrder, $salesOrder->details()->sole()];
    }

    private function makeProductVariant(Company $company, int $stock = 10, int $price = 100_000): ProductVariant
    {
        $product = Product::create([
            'company_id' => $company->id,
            'name' => 'Produk B2B Test',
            'slug' => 'produk-b2b-test-'.uniqid(),
            'status' => 'active',
        ]);
        $variant = Variant::create(['name' => 'Ukuran', 'value' => 'M']);

        return ProductVariant::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'sku' => 'B2B-'.uniqid(),
            'price' => $price,
            'stock' => $stock,
            'weight_grams' => 500,
        ]);
    }
}
