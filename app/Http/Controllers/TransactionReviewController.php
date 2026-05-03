<?php

namespace App\Http\Controllers;

use App\Models\TransactionDetail;
use App\Models\TransactionProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'transaction_detail_id' => ['required', 'integer', 'exists:transaction_details,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $detail = TransactionDetail::query()
            ->where('id', (int) $validated['transaction_detail_id'])
            ->where('transaction_id', (int) $validated['transaction_id'])
            ->whereHas('transaction', function ($q) use ($request) {
                $q->where('user_id', (int) $request->user()->id);
            })
            ->firstOrFail();

        $photoPaths = [];
        foreach ((array) $request->file('photos', []) as $photo) {
            $photoPaths[] = 'storage/' . $photo->store('reviews', 'public');
        }

        DB::transaction(function () use ($request, $validated, $photoPaths, $detail) {
            TransactionProductReview::query()->updateOrCreate(
                [
                    'transaction_id' => (int) $validated['transaction_id'],
                    'transaction_detail_id' => $detail->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'rating' => (int) $validated['rating'],
                    'message' => (string) ($validated['message'] ?? ''),
                    'photos' => $photoPaths,
                ],
            );
        });

        return back()->with('success', 'Ulasan berhasil disimpan.');
    }
}
