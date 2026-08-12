<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentProofController extends Controller
{
    public function customer(Request $request, Transaction $transaction): BinaryFileResponse
    {
        $owned = $request->user()
            ? (int) $transaction->user_id === (int) $request->user()->id
            : in_array((string) $transaction->order_id, array_merge(
                $request->session()->get('guest_owned_orders', []),
                $request->session()->get('verified_orders', [])
            ), true);
        abort_unless($owned, 403);

        return $this->file($transaction);
    }

    public function admin(Request $request, Transaction $transaction): BinaryFileResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasAdminPermission('transactions.show'), 403);
        if (strtolower((string) $user->role) !== 'admin') {
            abort_unless((int) $transaction->company_id === (int) $request->session()->get('admin_active_company_id'), 404);
        }

        return $this->file($transaction);
    }

    private function file(Transaction $transaction): BinaryFileResponse
    {
        $path = (string) $transaction->payment_proof_path;
        abort_if($path === '', 404);
        [$disk, $storedPath] = str_starts_with($path, 'private/')
            ? ['local', substr($path, 8)]
            : ['public', preg_replace('#^(?:storage/)+#', '', ltrim($path, '/'))];
        abort_unless(Storage::disk($disk)->exists($storedPath), 404);

        $response = response()->file(Storage::disk($disk)->path($storedPath), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
        $response->setPrivate();
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }
}
