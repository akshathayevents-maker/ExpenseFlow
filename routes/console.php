<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
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

// Purge sessions older than SESSION_LIFETIME.
// With lifetime=43200 (30 days), expired rows accumulate without this.
// Runs at 3 AM daily — low-traffic window.
Schedule::call(function () {
    $lifetime  = (int) config('session.lifetime', 43200);
    $threshold = now()->subMinutes($lifetime)->getTimestamp();
    DB::table('sessions')->where('last_activity', '<', $threshold)->delete();
})->dailyAt('03:00')->name('sessions:gc')->withoutOverlapping();
