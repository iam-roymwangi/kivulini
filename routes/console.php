<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('mpesa:expire-pending')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Process queued jobs (STK push, etc.) on each scheduler tick.
// On cPanel shared hosting where a persistent queue:work daemon isn't
// viable, the scheduler cron drives the queue every minute instead.
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=60')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
