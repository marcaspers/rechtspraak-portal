<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Each feed carries its own poll_interval_minutes in the database, so this single schedule
// entry (checked every minute) is enough to support arbitrary per-feed intervals without ever
// touching the scheduler when an admin changes a feed's interval.
Schedule::command('feeds:fetch-due')->everyMinute()->withoutOverlapping();

Schedule::command('digest:send')->hourly()->withoutOverlapping();
