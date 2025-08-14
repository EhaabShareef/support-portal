<?php

namespace App\Providers;

use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            app(ActivityLogger::class)->log($event->user, 'auth', 'login', null, 'User logged in');
        });

        Event::listen(Logout::class, function (Logout $event) {
            app(ActivityLogger::class)->log($event->user, 'auth', 'logout', null, 'User logged out');
        });
    }
}
