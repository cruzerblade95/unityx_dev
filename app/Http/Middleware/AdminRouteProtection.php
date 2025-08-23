<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRouteProtection
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
        // Define admin routes that should be public (no authentication required)
        $publicAdminRoutes = [
            'admin/login',
            'admin/logout',
            'admin/password/*',
            'admin/forgot-password',
            'admin/reset-password',
            'admin/register'
        ];

        // Check if this is a public admin route
        foreach ($publicAdminRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Check if this is an admin route (starts with /admin)
        if ($request->is('admin/*') || $request->is('admin')) {
            // If user is not authenticated as admin, redirect to admin login
            if (!Auth::guard('admin')->check()) {
                // If user is authenticated as regular user, redirect to user dashboard
                if (Auth::guard('web')->check()) {
                    return redirect()->route('user.dashboard')->with('error', 'You do not have permission to access the admin area.');
                }
                // If not authenticated at all, redirect to admin login
                return redirect()->route('admin.login')->with('error', 'Please login as admin to access this area.');
            }
        }

        return $next($request);
    }
}
