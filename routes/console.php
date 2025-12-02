<?php

use App\Jobs\ProccessExpiredHolds;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the ProccessExpiredHolds job to run every minute
Schedule::job(ProccessExpiredHolds::class)
->everyMinute()
->name("expired_holds")
->withoutOverlapping();