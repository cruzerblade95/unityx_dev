<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LocaleMiddleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/user.php'));
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(\App\Http\Middleware\CheckMigrationDomain::class);
        $middleware->web(LocaleMiddleware::class);
        $middleware->web(\App\Http\Middleware\AdminRouteProtection::class);
        $middleware->web(\App\Http\Middleware\GeneralRouteProtection::class);

        // Alias for middleware
        $middleware->alias([
            'require.payment.info' => \App\Http\Middleware\RequirePaymentInfo::class,
            'check.banned' => \App\Http\Middleware\CheckBannedUser::class,
            'require.kyc.verification' => \App\Http\Middleware\RequireKycVerification::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule) {
        // $schedule->command('investment:test-cron')->everyMinute();
        // $schedule->call(function () {
        //     dispatch(new \App\Jobs\TestInvestmentCron());
        // })->cron('0 1,5,9,13,17,21 * * *');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Determine redirect based on the requested route
            if ($request->is('admin/*') || $request->is('admin')) {
                return redirect()->route('admin.login')->with('error', 'Please login as admin to access this area.');
            } elseif ($request->is('user/*')) {
                return redirect()->route('user.login')->with('info', 'Please login to access this area.');
            } else {
                // For other routes, redirect to home page
                return redirect()->route('home');
            }
        });
    })->create();
