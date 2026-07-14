<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class BroadcastAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
          if (Auth::guard('recruiter')->check()) {
            Auth::shouldUse('recruiter');
        } elseif (Auth::guard('candidate')->check()) {
            Auth::shouldUse('candidate');
        } else {
            abort(403, 'Unauthenticated broadcast request.');
        }

        return $next($request);
    }
}
