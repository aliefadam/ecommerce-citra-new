<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $paidStatuses = ['paid', 'settlement', 'capture', 'process', 'kirim', 'selesai'];

        $users = User::query()
            ->where('role', 'user')
            ->whereNull('admin_role_id')
            ->withCount('transactions')
            ->withSum([
                'transactions as total_spent' => fn ($q) => $q->whereIn('status', $paidStatuses),
            ], 'grand_total')
            ->latest()
            ->get();

        return view('backend.users.index', compact('users'));
    }
}
