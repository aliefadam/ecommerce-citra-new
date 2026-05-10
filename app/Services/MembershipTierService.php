<?php

namespace App\Services;

use App\Models\MemberTier;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MembershipTierService
{
    public function spendingStatuses(): array
    {
        return ['paid', 'settlement', 'capture', 'process', 'processing', 'kirim', 'shipping', 'shipped', 'selesai', 'completed', 'delivered'];
    }

    public function tiers(): Collection
    {
        return MemberTier::query()
            ->where('is_active', true)
            ->orderBy('minimum_spending')
            ->orderBy('sort_order')
            ->get();
    }

    public function totalSpendingForUser(User $user): int
    {
        return (int) $user->transactions()
            ->whereIn(DB::raw('LOWER(status)'), $this->spendingStatuses())
            ->sum('grand_total');
    }

    public function resolveForUser(User $user, ?int $totalSpending = null): array
    {
        $totalSpending ??= $this->totalSpendingForUser($user);
        $tiers = $this->tiers();

        $currentTier = $tiers
            ->filter(fn (MemberTier $tier) => $totalSpending >= (int) $tier->minimum_spending)
            ->last();

        $nextTier = $tiers
            ->first(fn (MemberTier $tier) => $totalSpending < (int) $tier->minimum_spending);

        $currentMin = (int) ($currentTier?->minimum_spending ?? 0);
        $nextMin = (int) ($nextTier?->minimum_spending ?? 0);

        $progress = $tiers->isEmpty() ? 0 : 100;
        if ($nextTier) {
            $range = max(1, $nextMin - $currentMin);
            $progress = (int) max(0, min(100, round((($totalSpending - $currentMin) / $range) * 100)));
        }

        return [
            'total_spending' => $totalSpending,
            'current_tier' => $currentTier,
            'next_tier' => $nextTier,
            'progress_percent' => $progress,
            'current_minimum' => $currentMin,
            'next_minimum' => $nextMin,
            'remaining_to_next' => $nextTier ? max(0, $nextMin - $totalSpending) : 0,
        ];
    }
}
