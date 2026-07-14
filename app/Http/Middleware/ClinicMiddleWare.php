<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\RestResponse;

class ClinicMiddleWare
{
    use RestResponse;
    
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clinic = $request->user()->clinic;
    
        if (!$clinic) {
            return $this->customErrorRes('First, you must be complete Your Profile.');
        }
    
        // if ($clinic->verification != 1) {
        //     return $this->customErrorRes('Your Clinic has not been verified by the Fill-in.');
        // }

        // if ($clinic->status != 1) {
        //     return $this->customErrorRes('Your Clinic has been blocked by Fill-in. Please contact us for further assistance.');
        // }
    
        return $next($request);
    }
    
}

