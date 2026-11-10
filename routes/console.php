<?php

use App\Console\Commands\CheckOperations;
use App\Console\Commands\CreateOperationalBackup;
use App\Console\Commands\ExpireQuotations;
use App\Console\Commands\ReleaseExpiredCommerceReservations;
use App\Console\Commands\SendScheduledNewsletters;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('newsletter:send-scheduled-inline', function () {
    $this->call(SendScheduledNewsletters::class);
})->purpose('Send scheduled newsletter campaigns');

Artisan::command('mobile-spa:analytics-prune {--days=}', function () {
    $days = max(1, (int) ($this->option('days') ?: config('mobile-spa.analytics_retention_days')));
    $deleted = DB::table('mobile_analytics_events')->where('occurred_at', '<', now()->subDays($days))->delete();
    $this->info("Deleted {$deleted} mobile analytics events older than {$days} days.");
})->purpose('Prune privacy-safe mobile SPA analytics events');

Artisan::command('mobile-spa:analytics-summary {--hours=24}', function () {
    $hours = max(1, min(24 * 31, (int) $this->option('hours')));
    $query = DB::table('mobile_analytics_events')->where('occurred_at', '>=', now()->subHours($hours));
    $this->info("Mobile SPA analytics — last {$hours} hours");
    $this->table(
        ['Event', 'Count', 'Sessions'],
        (clone $query)
            ->select('event_name', DB::raw('COUNT(*) as event_count'), DB::raw('COUNT(DISTINCT session_hash) as session_count'))
            ->groupBy('event_name')
            ->orderByDesc('event_count')
            ->get()
            ->map(fn ($row) => [$row->event_name, $row->event_count, $row->session_count])
            ->all()
    );
    $errors = (clone $query)->whereIn('event_name', ['route_error', 'api_error', 'payment_failed'])->count();
    $this->line("Error events: {$errors}");
})->purpose('Show privacy-safe mobile SPA funnel and error counts');

Schedule::command(SendScheduledNewsletters::class)->everyMinute();
Schedule::command(ExpireQuotations::class)->hourly();
Schedule::command(CreateOperationalBackup::class)->dailyAt('01:30')->withoutOverlapping()->onOneServer();
Schedule::command(CheckOperations::class)->everyFiveMinutes()->withoutOverlapping();
Schedule::command(ReleaseExpiredCommerceReservations::class)->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('mobile-spa:analytics-prune')->dailyAt('02:15')->withoutOverlapping()->onOneServer();
