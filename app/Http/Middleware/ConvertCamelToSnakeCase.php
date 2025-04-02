<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ConvertCamelToSnakeCase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $request->replace($this->transformKeys($request->all()));

        return $next($request);
    }

    protected function transformKeys(array $input)
    {
        $transformed = [];

        foreach ($input as $key => $value) {
            $transformed[Str::snake($key)] = is_array($value)
                ? $this->transformKeys($value)
                : $value;
        }

        return $transformed;
    }
}
