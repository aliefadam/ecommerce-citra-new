<?php

namespace App\Console\Commands;

use App\Http\Controllers\NewsletterSubscriberController;
use App\Models\NewsletterCampaign;
use Illuminate\Console\Command;

class SendScheduledNewsletters extends Command
{
    protected $signature = 'newsletter:send-scheduled';
    protected $description = 'Send scheduled newsletter campaigns that are due';

    public function handle(): int
    {
        $campaigns = NewsletterCampaign::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        if ($campaigns->isEmpty()) {
            $this->info('No scheduled campaigns due.');
            return self::SUCCESS;
        }

        $controller = app(NewsletterSubscriberController::class);

        foreach ($campaigns as $campaign) {
            try {
                $controller->dispatchCampaign($campaign, null);
                $this->info("Campaign #{$campaign->id} sent.");
            } catch (\Throwable $e) {
                $campaign->update([
                    'status' => 'failed',
                    'last_error' => $e->getMessage(),
                ]);
                $this->error("Campaign #{$campaign->id} failed: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
