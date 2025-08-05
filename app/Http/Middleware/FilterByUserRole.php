<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FilterByUserRole
{
    /**
     * Handle an incoming request.
     *
     * This middleware can be used to automatically filter queries based on user role
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Store user context in request for easy access in components
        $request->merge([
            'user_role' => $user->roles->first()?->name,
            'user_department_id' => $user->department_id,
            'user_organization_id' => $user->organization_id,
        ]);

        return $next($request);
    }
}