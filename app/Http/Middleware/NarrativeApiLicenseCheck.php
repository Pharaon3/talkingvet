<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class NarrativeApiLicenseCheck
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $result = Gate::check('narrativeLicensed');

        if($result)
        {
            return $next($request);
        }
        else
        {
            return new JsonResponse(["error" => "Unauthorized - narrative license not found"], Response::HTTP_UNAUTHORIZED);
        }
    }
}
