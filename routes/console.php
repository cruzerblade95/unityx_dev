<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    logger("Trading Cron Running Function1");
})->everyMinute();

// Schedule::call(new \App\TradingCron())->cron('0 1,5,9,13,17,21 * * *');
Schedule::call(new \App\TradingCron())->everyMinute();
