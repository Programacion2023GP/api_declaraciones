<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaxExecutionTimeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $maxExecutionTime = env('MAX_EXECUTION_TIME', 60); // 60 segundos por defecto
        ini_set('max_execution_time', $maxExecutionTime);

        return $next($request);
    }
}
