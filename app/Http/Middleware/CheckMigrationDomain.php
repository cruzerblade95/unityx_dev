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

        // Debug: Log the host for testing
        \Log::info('CheckMigrationDomain - Host: ' . $host);

        // Check if the host contains unityx.dev
        if (str_contains(strtolower($host), 'unityx.dev')) {
            \Log::info('CheckMigrationDomain - unityx.dev domain detected');

            // Skip migration notice for certain routes (like API endpoints)
            $skipRoutes = [
                'api/',
                'migration-notice',
                'test-domain',
                'test-migration',
                '_debugbar',
                'livewire/',
                'storage/',
                'assets/',
                'vendor/',
                'build/'
            ];

            $currentPath = $request->path();
            \Log::info('CheckMigrationDomain - Current path: ' . $currentPath);

            // Don't redirect if already on migration notice page or excluded routes
            foreach ($skipRoutes as $skipRoute) {
                if (str_starts_with($currentPath, $skipRoute)) {
                    \Log::info('CheckMigrationDomain - Skipping route: ' . $skipRoute);
                    return $next($request);
                }
            }

            \Log::info('CheckMigrationDomain - Showing migration notice');
            // Redirect to migration notice
            return response()->view('migration_notice');
        }

        \Log::info('CheckMigrationDomain - Not unityx.dev domain, continuing');
        return $next($request);
    }
}
