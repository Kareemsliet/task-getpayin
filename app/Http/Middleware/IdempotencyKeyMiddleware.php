<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKeyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key',null);

        if (!$key) {
            return errorJsonResponse('Idempotency-Key header is required', null, 400);
        }

        if(Cache::has("idempotency:{$key}"))
        {
            return Cache::get("idempotency:{$key}");
        }

        $response = $next($request);

        if($response->getStatusCode() >= 200)
        {
            Cache::put("idempotency:{$key}", $response, 3600);
        }

        return $response;
    }
}
