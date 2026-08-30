<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\ReturnRequest;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionProductReview;
use App\Models\TransactionTaxInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionCompanyIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Company $activeCompany;

    private Company $otherCompany;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCompany = Company::query()->where('slug', 'boq')->firstOrFail();
        $this->otherCompany = Company::create([
            'name' => 'PT Perusahaan Lain',
            'slug' => 'perusahaan-lain',
            'invoice_prefix' => 'LAIN',
            'is_active' => true,
            'sort_order' => 2,
        ]);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($this->admin)
            ->withSession(['admin_active_company_id' => $this->activeCompany->id]);
    }

    public function test_transaction_lists_and_bulk_labels_only_include_active_company(): void
    {
        $activeTransaction = $this->makeTransaction($this->activeCompany, 'ACTIVE');
        $otherTransaction = $this->makeTransaction($this->otherCompany, 'OTHER');

        $this->get(route('transactions.index'))
            ->assertOk()
            ->assertSee($activeTransaction->invoice_no, false)
            ->assertDontSee($otherTransaction->invoice_no, false);

        $this->get(route('transactions.bulk-shipping-label', [
            'ids' => $activeTransaction->id.','.$otherTransaction->id,
        ]))
            ->assertOk()
            ->assertSee($activeTransaction->order_id, false)
            ->assertDontSee($otherTransaction->order_id, false);
    }

    public function test_superadmin_cannot_read_or_mutate_transaction_from_another_active_company(): void
    {
        $transaction = $this->makeTransaction($this->otherCompany, 'GUARDED', [
            'source' => Transaction::SOURCE_MANUAL,
            'status' => 'paid',
            'payment_type' => 'manual_transfer',
        ]);

        $this->get(route('transactions.show', $transaction))->assertNotFound();
        $this->get(route('transactions.payment-proof', $transaction))->assertNotFound();
        $this->get(route('transactions.shipping-label', $transaction))->assertNotFound();
        $this->patchJson(route('transactions.process', $transaction))->assertNotFound();
        $this->patchJson(route('transactions.ship', $transaction), [
            'tracking_number' => 'RESI-LINTAS-PT',
        ])->assertNotFound();
        $this->patch(route('transactions.verify-payment', $transaction), [
            'action' => 'approve',
        ])->assertNotFound();
        $this->patch(route('transactions.manual-payment.update', $transaction), [
            'action' => 'mark_paid',
        ])->assertNotFound();
        $this->patch(route('transactions.manual-shipping.update', $transaction), [
            'shipping_status' => 'shipped',
        ])->assertNotFound();

        $this->assertSame('paid', $transaction->fresh()->status);
        $this->assertNull($transaction->fresh()->tracking_number);
    }

    public function test_transaction_derived_lists_only_include_active_company(): void
    {
        [$activeTransaction, $activeDetail] = $this->makeTransactionWithDetail($this->activeCompany, 'DERIVED-ACTIVE');
        [$otherTransaction, $otherDetail] = $this->makeTransactionWithDetail($this->otherCompany, 'DERIVED-OTHER');

        $activeTaxInvoice = $this->makeTaxInvoice($activeTransaction, 'PT Pajak Aktif');
        $otherTaxInvoice = $this->makeTaxInvoice($otherTransaction, 'PT Pajak Lain');
        $activeReturn = $this->makeReturnRequest($activeTransaction, 'RET-ACTIVE');
        $otherReturn = $this->makeReturnRequest($otherTransaction, 'RET-OTHER');
        $activeReview = $this->makeReview($activeTransaction, $activeDetail, 'Ulasan perusahaan aktif');
        $otherReview = $this->makeReview($otherTransaction, $otherDetail, 'Ulasan perusahaan lain');

        $this->get(route('tax-invoices.index'))
            ->assertOk()
            ->assertSee($activeTaxInvoice->taxpayer_name, false)
            ->assertDontSee($otherTaxInvoice->taxpayer_name, false);

        $this->get(route('return-requests.index'))
            ->assertOk()
            ->assertSee($activeReturn->request_no, false)
            ->assertDontSee($otherReturn->request_no, false);

        $this->get(route('product-reviews.index'))
            ->assertOk()
            ->assertSee($activeReview->message, false)
            ->assertDontSee($otherReview->message, false)
            ->assertSee('Semua (1)', false);
    }

    public function test_superadmin_cannot_mutate_transaction_derived_data_from_another_company(): void
    {
        [$transaction, $detail] = $this->makeTransactionWithDetail($this->otherCompany, 'DERIVED-GUARDED');
        $taxInvoice = $this->makeTaxInvoice($transaction, 'PT Pajak Terjaga');
        $returnRequest = $this->makeReturnRequest($transaction, 'RET-GUARDED');
        $review = $this->makeReview($transaction, $detail, 'Ulasan terjaga');

        $this->get(route('tax-invoices.show', $taxInvoice))->assertNotFound();
        $this->patch(route('tax-invoices.process', $taxInvoice))->assertNotFound();
        $this->patch(route('tax-invoices.reject', $taxInvoice))->assertNotFound();
        $this->post(route('tax-invoices.upload', $taxInvoice))->assertNotFound();
        $this->post(route('tax-invoices.send', $taxInvoice))->assertNotFound();
        $this->get(route('tax-invoices.download', $taxInvoice))->assertNotFound();

        $this->patch(route('return-requests.update', $returnRequest), [
            'status' => 'disetujui',
        ])->assertNotFound();
        $this->patch(route('product-reviews.toggle', $review))->assertNotFound();
        $this->delete(route('product-reviews.destroy', $review))->assertNotFound();

        $this->assertSame(TransactionTaxInvoice::STATUS_REQUESTED, $taxInvoice->fresh()->status);
        $this->assertSame('menunggu', $returnRequest->fresh()->status);
        $this->assertFalse($review->fresh()->is_hidden);
    }

    private function makeTransaction(Company $company, string $suffix, array $overrides = []): Transaction
    {
        [$transaction] = $this->makeTransactionWithDetail($company, $suffix, $overrides);

        return $transaction;
    }

    private function makeTransactionWithDetail(Company $company, string $suffix, array $overrides = []): array
    {
        $customer = User::factory()->create();
        $transaction = Transaction::create(array_merge([
            'company_id' => $company->id,
            'user_id' => $customer->id,
            'invoice_no' => 'INV-'.$suffix,
            'order_id' => 'ORD-'.$suffix,
            'payment_type' => 'bank_transfer',
            'payment_method' => 'BCA',
            'status' => 'pending',
            'subtotal_amount' => 100_000,
            'shipping_cost' => 10_000,
            'grand_total' => 110_000,
            'shipping_label' => 'JNE REG',
            'shipping_recipient_name' => 'Customer '.$suffix,
            'shipping_phone' => '08123456789',
            'shipping_address_line' => 'Jl. Pengujian No. 1',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal_code' => '12345',
        ], $overrides));

        $detail = TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'product_name' => 'Produk '.$suffix,
            'variant_name' => 'Default',
            'price' => 100_000,
            'quantity' => 1,
            'subtotal' => 100_000,
        ]);

        return [$transaction, $detail];
    }

    private function makeTaxInvoice(Transaction $transaction, string $taxpayerName): TransactionTaxInvoice
    {
        return TransactionTaxInvoice::create([
            'transaction_id' => $transaction->id,
            'requested_by_user_id' => $transaction->user_id,
            'status' => TransactionTaxInvoice::STATUS_REQUESTED,
            'taxpayer_name' => $taxpayerName,
            'taxpayer_number' => '123456789012345',
            'taxpayer_address' => 'Jl. Pajak No. 1',
            'taxpayer_email' => 'tax-'.uniqid().'@example.test',
            'requested_at' => now(),
        ]);
    }

    private function makeReturnRequest(Transaction $transaction, string $requestNo): ReturnRequest
    {
        return ReturnRequest::create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'request_no' => $requestNo,
            'type' => 'refund',
            'status' => 'menunggu',
            'refund_amount' => 100_000,
            'reason' => 'Alasan pengujian',
        ]);
    }

    private function makeReview(Transaction $transaction, TransactionDetail $detail, string $message): TransactionProductReview
    {
        return TransactionProductReview::create([
            'transaction_id' => $transaction->id,
            'transaction_detail_id' => $detail->id,
            'user_id' => $transaction->user_id,
            'rating' => 5,
            'message' => $message,
            'photos' => [],
        ]);
    }
}
