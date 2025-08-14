<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     */
    /**
     * This namespace is applied to your controller routes.
     *
     * @var string
     */
    protected $namespace = 'App\\Http\\Controllers';

    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/user.php'));

            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        });
    }
}
