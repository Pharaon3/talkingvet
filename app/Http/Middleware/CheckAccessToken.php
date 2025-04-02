<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccessToken
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
        // Check if the request has a bearer token
        if (!$request->bearerToken()) {
            return response()->json(['statusMessage' => 'Unauthorized - no access token'], 401);
        }

        // Attempt to authenticate user with the token
        if (Auth::guard('sanctum')->check()) {
            return $next($request);
        }

        return response()->json(['statusMessage' => 'Unauthorized - invalid access token'], 401);
    }
}
