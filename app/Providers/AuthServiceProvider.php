<?php

namespace App\Providers;

use App\Models\Schedule;
use App\Models\ScheduleEventType;
use App\Policies\SchedulePolicy;
use App\Policies\ScheduleEventTypePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Schedule::class => SchedulePolicy::class,
        ScheduleEventType::class => ScheduleEventTypePolicy::class,
    ];

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define additional gates if needed
        Gate::define('access-schedule-module', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin', 'Client']);
        });

        Gate::define('manage-schedules', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin']);
        });

        Gate::define('manage-schedule-event-types', function ($user) {
            return $user->hasAnyRole(['Super Admin', 'Admin']);
        });
    }
}