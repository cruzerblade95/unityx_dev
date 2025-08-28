<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeneralRouteProtection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip protection for public routes
        $publicRoutes = [
            '/',
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password/*',
            'auth/*',
            'user/login',
            'user/logout',
            'user/register',
            'user/forgot-password',
            'user/password/reset/*',
            'admin/login',
            'admin/logout',
            'api/*',
            'up', // Health check
            'sanctum/*', // Sanctum CSRF
            'connect-telegram', // Telegram connection route
            'account-recovery',
            'account-recovery/*',
            'migration-notice',
        ];

        // Check if current route is public
        foreach ($publicRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // If accessing user routes, let UserMiddleware handle it
        if ($request->is('user/*')) {
            return $next($request);
        }

        // If accessing admin routes, let AdminRouteProtection handle it
        if ($request->is('admin/*') || $request->is('admin')) {
            return $next($request);
        }

        // For all other protected routes, check authentication
        if (!Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            // If not authenticated and not accessing user routes, redirect to login
            return redirect()->route('user.login')->with('info', 'Please login to access this area.');
        }

        // If authenticated as regular user but trying to access non-user area
        if (Auth::guard('web')->check() && !$request->is('user/*')) {
            // Redirect to user dashboard
            return redirect()->route('user.dashboard');
        }

        return $next($request);
    }
}
