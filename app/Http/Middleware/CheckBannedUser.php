<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBannedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and banned
        if (Auth::check() && Auth::user()->is_banned) {
            Auth::logout();

            return redirect()->route('user.banned')
                ->with('banReason', Auth::user()->ban_reason)
                ->with('bannedAt', Auth::user()->banned_at);
        }

        return $next($request);
    }
}
