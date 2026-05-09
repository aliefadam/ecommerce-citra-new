<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'admin' || (int) $transaction->user_id === (int) $user->id), 403);

        $transaction->load(['user', 'details']);

        return view('invoices.print', compact('transaction'));
    }
}
