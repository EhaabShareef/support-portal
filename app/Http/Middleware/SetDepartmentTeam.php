<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middlewares\PermissionMiddleware;

class SetDepartmentTeam
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && session()->has('department_id')) {
            // tell Spatie what "team" to use
            setPermissionsTeamId(session('department_id'));
        }

        return $next($request);
    }
}
