<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMigrationDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Check if the host contains unityx.dev
        if (str_contains(strtolower($host), 'unityx.dev')) {
            // Skip migration notice for certain routes (like API endpoints)
            $skipRoutes = [
                'api/',
                'migration-notice',
                '_debugbar',
                'livewire/',
                'storage/',
                'assets/'
            ];

            $currentPath = $request->path();

            // Don't redirect if already on migration notice page or excluded routes
            foreach ($skipRoutes as $skipRoute) {
                if (str_starts_with($currentPath, $skipRoute)) {
                    return $next($request);
                }
            }

            // Redirect to migration notice
            return response()->view('migration_notice');
        }

        return $next($request);
    }
}
