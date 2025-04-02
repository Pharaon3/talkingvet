<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InternalApiAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('sanctum')->user();
        $permission = Permission::where(['user_id' => $user->id])->first();
        if($permission->role == 2){
            return $next($request);
        }
        return response()->json([
           'statusMessage' => 'Forbidden - not allowed access.'
        ], 403);
    }
}
