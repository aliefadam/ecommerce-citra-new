<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\TransactionProductReview;
use App\Services\ImageOptimizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminProductReviewController extends Controller
{
    use ScopesToActiveCompany;

    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['all', 'visible', 'hidden'], true)) {
            $status = 'all';
        }

        $reviews = TransactionProductReview::query()
            ->whereHas('transaction', fn ($query) => $query->where('company_id', $this->activeCompanyId()))
            ->with([
                'user:id,name,email',
                'transaction:id,invoice_no,order_id',
                'transactionDetail:id,transaction_id,product_id,product_name,variant_name,image',
            ])
            ->when($status === 'visible', fn ($query) => $query->where('is_hidden', false))
            ->when($status === 'hidden', fn ($query) => $query->where('is_hidden', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => $this->reviewsForActiveCompany()->count(),
            'visible' => $this->reviewsForActiveCompany()->where('is_hidden', false)->count(),
            'hidden' => $this->reviewsForActiveCompany()->where('is_hidden', true)->count(),
        ];

        return view('backend.product-reviews.index', compact('reviews', 'status', 'counts'));
    }

    public function toggle(TransactionProductReview $review)
    {
        $this->guardReviewOwnership($review);

        $review->update([
            'is_hidden' => ! $review->is_hidden,
            'hidden_at' => $review->is_hidden ? null : now(),
        ]);

        return back()->with('success', $review->is_hidden ? 'Ulasan disembunyikan.' : 'Ulasan ditampilkan kembali.');
    }

    public function destroy(TransactionProductReview $review, ImageOptimizer $imageOptimizer)
    {
        $this->guardReviewOwnership($review);

        foreach ((array) $review->photos as $photo) {
            $imageOptimizer->deletePublicFile((string) $photo);
        }

        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }

    private function reviewsForActiveCompany(): Builder
    {
        return TransactionProductReview::query()
            ->whereHas('transaction', fn ($query) => $query->where('company_id', $this->activeCompanyId()));
    }

    private function guardReviewOwnership(TransactionProductReview $review): void
    {
        $this->guardCompanyOwnership($review->transaction()->value('company_id'));
    }
}
