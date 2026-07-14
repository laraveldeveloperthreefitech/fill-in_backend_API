<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\TimezoneHelper;
use Illuminate\Support\Facades\Session;

class SetAdminTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(Session::has('user_timezone'));
        if (Session::has('user_timezone')) {
            $timezone = Session::get('user_timezone');
            $validatetimezone = TimezoneHelper::sanitize($timezone); 
            config(['app.timezone' => $validatetimezone]);
            date_default_timezone_set($validatetimezone);
        }
        return $next($request);
    }
}
