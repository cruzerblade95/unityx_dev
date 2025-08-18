<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            if ($request->is('admin/*')) {
                // For admin routes, redirect to main login
                return '/admin/login';
            }
            if ($request->is('user/*')) {
                // For user routes, redirect to user login
                return '/user/login';
            }
            // For other routes, use the named route
            return route('/');
        }
        return null;
    }
}
