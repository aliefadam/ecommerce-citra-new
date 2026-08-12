<?php

use App\Console\Commands\CheckOperations;
use App\Console\Commands\CreateOperationalBackup;
use App\Console\Commands\ExpireQuotations;
use App\Console\Commands\SendScheduledNewsletters;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('newsletter:send-scheduled-inline', function () {
    $this->call(SendScheduledNewsletters::class);
})->purpose('Send scheduled newsletter campaigns');

Schedule::command(SendScheduledNewsletters::class)->everyMinute();
Schedule::command(ExpireQuotations::class)->hourly();
Schedule::command(CreateOperationalBackup::class)->dailyAt('01:30')->withoutOverlapping()->onOneServer();
Schedule::command(CheckOperations::class)->everyFiveMinutes()->withoutOverlapping();
