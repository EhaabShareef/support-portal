<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\EventServiceProvider::class,
    ])
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    ]);

        // Global request context middleware
        $middleware->append(\App\Http\Middleware\RequestContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Log unhandled exceptions as issue activities
        $exceptions->report(function (Throwable $e) {
            try {
                app(\App\Services\ActivityLogger::class)
                    ->log(auth()->user(), 'issues', 'exception', null, $e->getMessage());
            } catch (Throwable $ex) {
                // ignore
            }
        });
    })->create();
