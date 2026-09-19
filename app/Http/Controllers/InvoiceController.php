<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ScopesToActiveCompany;
use App\Models\Transaction;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ScopesToActiveCompany;

    public function show(Request $request, Transaction $transaction)
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->canAccessAdminPanel()) {
            abort_unless($user->hasAdminPermission('transactions.show'), 403);
            $this->guardCompanyOwnership($transaction->company_id);
        } else {
            abort_unless((int) $transaction->user_id === (int) $user->id, 403);
        }

        $transaction->load(['user', 'details']);

        return view('invoices.print', compact('transaction'));
    }
}
