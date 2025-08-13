<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check()) {
            // Optionally check for user role here
            return $next($request);
        }
        return redirect()->route('user.login');
    }
}
