<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use App\Models\QuotationStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireQuotations extends Command
{
    protected $signature = 'quotations:expire';
    protected $description = 'Mark quotations past their valid_until date as expired';

    public function handle(): int
    {
        $quotations = Quotation::query()
            ->whereIn('status', [
                Quotation::STATUS_DRAFT,
                Quotation::STATUS_SENT,
                Quotation::STATUS_ACCEPTED,
                Quotation::STATUS_PARTIALLY_CONVERTED,
            ])
            ->where('valid_until', '<', now()->startOfDay())
            ->get();

        if ($quotations->isEmpty()) {
            $this->info('No quotations to expire.');
            return self::SUCCESS;
        }

        foreach ($quotations as $quotation) {
            DB::transaction(function () use ($quotation) {
                $fromStatus = $quotation->status;
                $quotation->update(['status' => Quotation::STATUS_EXPIRED]);

                QuotationStatusHistory::create([
                    'quotation_id' => $quotation->id,
                    'user_id' => null,
                    'from_status' => $fromStatus,
                    'to_status' => Quotation::STATUS_EXPIRED,
                    'note' => 'Kedaluwarsa otomatis, valid_until terlewati.',
                    'created_at' => now(),
                ]);
            });

            $this->info("Quotation #{$quotation->id} ({$quotation->quotation_no}) expired.");
        }

        return self::SUCCESS;
    }
}
