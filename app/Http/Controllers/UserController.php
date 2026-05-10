<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MembershipTierService;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(MembershipTierService $membershipTierService)
    {
        $paidStatuses = $membershipTierService->spendingStatuses();

        $users = User::query()
            ->where('role', 'user')
            ->whereNull('admin_role_id')
            ->withCount('transactions')
            ->withSum([
                'transactions as total_spent' => fn ($q) => $q->whereIn(DB::raw('LOWER(status)'), $paidStatuses),
            ], 'grand_total')
            ->latest()
            ->get();

        $users->transform(function (User $user) use ($membershipTierService) {
            $membership = $membershipTierService->resolveForUser($user, (int) ($user->total_spent ?? 0));
            $user->membership_tier_name = $membership['current_tier']?->name ?? 'Member';

            return $user;
        });

        return view('backend.users.index', compact('users'));
    }
}
