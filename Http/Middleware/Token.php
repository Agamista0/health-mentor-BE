<?php

namespace App\Http\Middleware;

use App\Response\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Token
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Use the 'api' guard explicitly for API authentication
        // if (Auth::guard('api')->user()->currentAccessToken()->abilities[0] == "verify_otp" ||
        //     Auth::guard('api')->user()->currentAccessToken()->abilities[0] == "register") {
        //     return (new ApiResponse(401, 'This Token used for verify otp or register only', []))->send();
        // }

        return $next($request);
    }
}
