<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            // Optionally check for admin role here
            return $next($request);
        }
        return redirect()->route('admin.login');
    }
}
