<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Automatically sync AMAPI fleet every 15 minutes
Schedule::command('mdm:sync-devices')->everyFifteenMinutes();
