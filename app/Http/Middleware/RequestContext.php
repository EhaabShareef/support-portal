<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestContext
{
    public function handle(Request $request, Closure $next)
    {
        $requestId = (string) Str::uuid();
        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('ip_address', $request->ip());
        $request->attributes->set('user_agent', $request->userAgent());
        return $next($request);
    }
}
