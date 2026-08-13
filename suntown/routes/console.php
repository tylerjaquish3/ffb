<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keeps injury statuses fresh through the week without a manual sync.
// Requires the system cron to be wired to `php artisan schedule:run` per
// Laravel's usual scheduler setup.
Schedule::command('sportradar:injuries')->everyFourHours();
