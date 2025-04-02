<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GenAI_TestAPISecretMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): JsonResponse|Response
    {
        if ($request->input('token') !== config('app.gen_ai_data.test_api_token'))
        {
            return new JsonResponse(['error'=>'Test Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
