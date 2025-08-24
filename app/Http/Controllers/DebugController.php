<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DebugController extends Controller
{
    public function routes()
    {
        $routes = collect(Route::getRoutes())->map(function ($route) {
            return [
                'uri' => $route->uri(),
                'methods' => $route->methods(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        })->toArray();

        return response()->json([
            'total_routes' => count($routes),
            'api_routes' => array_filter($routes, function($route) {
                return str_starts_with($route['uri'], 'api/');
            }),
            'timestamp' => now()
        ]);
    }

    public function info(Request $request)
    {
        return response()->json([
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug'),
            'app_url' => config('app.url'),
            'request_url' => $request->fullUrl(),
            'server_time' => now(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version()
        ]);
    }
}
