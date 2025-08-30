<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsRegistry
{
    private const CACHE_KEY = 'settings_registry';
    private const CACHE_TTL = 3600; // 1 hour

    private array $modules = [];

    public function __construct()
    {
        $this->registerDefaultModules();
    }

    /**
     * Register a settings module
     */
    public function register(string $key, array $config): void
    {
        $this->modules[$key] = array_merge([
            'enabled' => true,
            'sort_order' => 999,
            'permissions' => ['settings.read'],
            'icon' => 'heroicon-o-cog-6-tooth',
        ], $config);

        $this->clearCache();
    }

    /**
     * Get all registered modules
     */
    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return collect($this->modules)
                ->filter(fn($module) => $module['enabled'])
                ->sortBy('sort_order')
                ->values();
        });
    }

    /**
     * Get a specific module
     */
    public function get(string $key): ?array
    {
        return $this->modules[$key] ?? null;
    }

    /**
     * Check if a module exists
     */
    public function has(string $key): bool
    {
        return isset($this->modules[$key]);
    }

    /**
     * Get modules accessible to a user
     */
    public function accessibleToUser($user): Collection
    {
        return $this->all()->filter(function ($module) use ($user) {
            foreach ($module['permissions'] as $permission) {
                if (!$user->can($permission)) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Register default modules
     */
    private function registerDefaultModules(): void
    {
        $this->register('general', [
            'title' => 'General',
            'description' => 'App-wide settings and hotlines',
            'icon' => 'heroicon-o-cog-6-tooth',
            'component' => 'admin.settings.modules.general-settings',
            'sort_order' => 1,
            'permissions' => ['settings.read'],
        ]);

        $this->register('ticket', [
            'title' => 'Ticket',
            'description' => 'Ticket workflow, colors, and statuses',
            'icon' => 'heroicon-o-ticket',
            'component' => 'admin.settings.modules.ticket-settings',
            'sort_order' => 2,
            'permissions' => ['settings.read'],
        ]);

        $this->register('organization', [
            'title' => 'Organization',
            'description' => 'Department groups and departments',
            'icon' => 'heroicon-o-building-office',
            'component' => 'admin.settings.modules.organization-settings',
            'sort_order' => 3,
            'permissions' => ['settings.read'],
        ]);

        $this->register('contracts', [
            'title' => 'Contracts',
            'description' => 'Contract types and statuses',
            'icon' => 'heroicon-o-document-text',
            'component' => 'admin.settings.modules.contract-settings',
            'sort_order' => 4,
            'permissions' => ['settings.read'],
        ]);

        $this->register('hardware', [
            'title' => 'Hardware',
            'description' => 'Hardware types and statuses',
            'icon' => 'heroicon-o-computer-desktop',
            'component' => 'admin.settings.modules.hardware-settings',
            'sort_order' => 5,
            'permissions' => ['settings.read'],
        ]);

        $this->register('schedule', [
            'title' => 'Schedule',
            'description' => 'Weekend days and event types',
            'icon' => 'heroicon-o-calendar-days',
            'component' => 'admin.settings.modules.schedule-settings',
            'sort_order' => 6,
            'permissions' => ['settings.read'],
        ]);

        $this->register('users', [
            'title' => 'Users',
            'description' => 'User management defaults',
            'icon' => 'heroicon-o-users',
            'component' => 'admin.settings.modules.user-settings',
            'sort_order' => 7,
            'permissions' => ['settings.read'],
        ]);
    }

    /**
     * Get module configuration for the shell
     */
    public function getModuleConfig(string $key): array
    {
        $module = $this->get($key);
        
        if (!$module) {
            return [];
        }

        return [
            'label' => $module['title'],
            'description' => $module['description'],
            'icon' => $module['icon'],
            'component' => $module['component'],
        ];
    }

    /**
     * Get all module configurations for the shell
     */
    public function getAllModuleConfigs(): array
    {
        return $this->all()->mapWithKeys(function ($module, $key) {
            return [$key => $this->getModuleConfig($key)];
        })->toArray();
    }
}
