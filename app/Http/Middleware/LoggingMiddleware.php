<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next , string $action = null): Response
    {
        $start = microtime(true);

        $context = [
            'action' => $action,
            'user' => optional($request->user())->id ?? $request->ip(),
            'path' => $request->path(),
            'method' => $request->method(),
            'input' => $request->all(),
            'time' => now()->format('Y-m-d H:i:s')
        ];

        Log::Channel('aspect')->info("[$action] BEFORE" , $context);

        try {
            $response = $next($request);
        }catch(\Throwable $exception)
        {
            Log::Channel('aspect')->info("[$action] ERROR" , ['error' => $exception->getMessage()]);
            throw $exception;
        }

        $duration = microtime(true) - $start;
        $contextAfter = [
            'timeExecutionInMicroSecond' => $duration,
            'status' => $response->getStatusCode(),
        ];

        Log::Channel('aspect')->info("[$action] AFTER" , $contextAfter);

        return $response;
    }
}
