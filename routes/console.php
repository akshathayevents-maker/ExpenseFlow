<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Run stock check every 2 hours
Schedule::command('app:check-stock')->everyTwoHours();

// Send daily summary at 9 PM
Schedule::command('app:daily-summary')->dailyAt('21:00');

// Remind about pending approvals at 10 AM and 4 PM
Schedule::command('app:remind-pending')->twiceDaily(10, 16);

// Delete temp QR files older than 24 hours — runs every hour
Schedule::command('app:cleanup-temp-qr')->hourly();
