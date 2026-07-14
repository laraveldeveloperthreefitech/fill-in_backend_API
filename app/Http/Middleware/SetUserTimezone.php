<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Helpers\TimezoneHelper;

class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = $request->header('timezone');
        if ($timezone && $timezone != config('app.timezone')) {
            $validatetimezone = TimezoneHelper::sanitize($timezone); 
            config(['app.timezone' => $validatetimezone]);
            date_default_timezone_set($validatetimezone);
        } 
        return $next($request);
    }
}
