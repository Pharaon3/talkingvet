<?php

namespace App\Http\Middleware;

use App\Http\Controllers\NvoqNetworkController;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class GenAI_APIAuthMiddleware
{
    /**
     * Handle an incoming request.
     * Internally Supported Authentications:
     *  - Basic Auth Header (regular format)
     *  - Basic Auth token supplied as a json parameter called `userAuthString` with the request (fallback)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(
            !$request->has(['country'])
            || !array_key_exists($request['country'], config('app.nvoq_servers'))
        )
        {
            return
                new JsonResponse(['error'=>'country parameter missing or incorrect, values are (' . Arr::join(array_keys(config('app.nvoq_servers')), ", ") . ')' ],
                    \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST);
        }

        /* try login user */
        $result = NvoqNetworkController::GenAI_LoginWithBasicAuthentication($request);

        if(!$result)
        {
            Log::debug('[GenAI API AUTH] failed to login to nvoq servers');
            return
                new JsonResponse(['error'=>'Unauthorized'],
                    \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
        }

        Log::debug('[GenAI API AUTH] logged in to nvoq successfully | user: ' . session("username"));
        return $next($request);
    }
}
