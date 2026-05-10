<?php

namespace App\Http\Controllers;

use App\Models\TransactionProductReview;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;

class AdminProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        if (!in_array($status, ['all', 'visible', 'hidden'], true)) {
            $status = 'all';
        }

        $reviews = TransactionProductReview::query()
            ->with([
                'user:id,name,email',
                'transaction:id,invoice_no,order_id',
                'transactionDetail:id,transaction_id,product_id,product_name,variant_name,image',
            ])
            ->when($status === 'visible', fn($query) => $query->where('is_hidden', false))
            ->when($status === 'hidden', fn($query) => $query->where('is_hidden', true))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => TransactionProductReview::query()->count(),
            'visible' => TransactionProductReview::query()->where('is_hidden', false)->count(),
            'hidden' => TransactionProductReview::query()->where('is_hidden', true)->count(),
        ];

        return view('backend.product-reviews.index', compact('reviews', 'status', 'counts'));
    }

    public function toggle(TransactionProductReview $review)
    {
        $review->update([
            'is_hidden' => !$review->is_hidden,
            'hidden_at' => $review->is_hidden ? null : now(),
        ]);

        return back()->with('success', $review->is_hidden ? 'Ulasan disembunyikan.' : 'Ulasan ditampilkan kembali.');
    }

    public function destroy(TransactionProductReview $review, ImageOptimizer $imageOptimizer)
    {
        foreach ((array) $review->photos as $photo) {
            $imageOptimizer->deletePublicFile((string) $photo);
        }

        $review->delete();

        return back()->with('success', 'Ulasan berhasil dihapus.');
    }
}
