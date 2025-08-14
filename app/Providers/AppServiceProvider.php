<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\SettingsRepositoryInterface;
use App\Services\SettingsRepository;
use App\Observers\UserActivityObserver;
use App\Models\Ticket;
use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Models\Schedule;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SettingsRepositoryInterface::class, SettingsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Ticket::observe(UserActivityObserver::class);
        Organization::observe(UserActivityObserver::class);
        OrganizationContract::observe(UserActivityObserver::class);
        OrganizationHardware::observe(UserActivityObserver::class);
        Schedule::observe(UserActivityObserver::class);
        User::observe(UserActivityObserver::class);
        Role::observe(UserActivityObserver::class);
        Permission::observe(UserActivityObserver::class);
        Setting::observe(UserActivityObserver::class);
    }
}
