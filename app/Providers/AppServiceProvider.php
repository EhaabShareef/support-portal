<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\SettingsRepositoryInterface;
use App\Services\SettingsRepository;
use App\Helpers\FlysystemAutoloader;

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
        // Register custom Flysystem autoloader to handle class conflicts
        FlysystemAutoloader::register();
        
        // Log conflict information for debugging
        if (FlysystemAutoloader::hasConflicts()) {
            \Log::info('Flysystem class conflicts detected', FlysystemAutoloader::getConflictInfo());
        }
    }
}
