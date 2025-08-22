<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(LocaleMiddleware::class);
    })
    ->withSchedule(function (Schedule $schedule) {
        // $schedule->command('investment:test-cron')->everyMinute();
        // $schedule->call(function () {
        //     dispatch(new \App\Jobs\TestInvestmentCron());
        // })->cron('0 1,5,9,13,17,21 * * *');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->redirectTo(function ($request) {
            return $request->expectsJson() ? null : route('home');
        });
    })->create();
