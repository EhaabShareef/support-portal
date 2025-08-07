<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class PermissionService
{
    /**
     * Check if the current user has a specific permission
     */
    public static function can(string $permission): bool
    {
        return Auth::check() && Auth::user()->can($permission);
    }

    /**
     * Check if the current user has any of the specified permissions
     */
    public static function canAny(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (Auth::user()->can($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the current user has all of the specified permissions
     */
    public static function canAll(array $permissions): bool
    {
        if (!Auth::check()) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!Auth::user()->can($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the current user has a specific role
     */
    public static function hasRole(string $role): bool
    {
        return Auth::check() && Auth::user()->hasRole($role);
    }

    /**
     * Check if the current user has any of the specified roles
     */
    public static function hasAnyRole(array $roles): bool
    {
        return Auth::check() && Auth::user()->hasAnyRole($roles);
    }

    /**
     * Get all permissions for a module
     */
    public static function getModulePermissions(string $module): array
    {
        return Permission::where('name', 'like', $module . '.%')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Check if user can perform CRUD operations on a module
     */
    public static function canCrud(string $module): array
    {
        $user = Auth::user();
        
        if (!$user) {
            return [
                'create' => false,
                'read' => false,
                'update' => false,
                'delete' => false,
            ];
        }

        return [
            'create' => $user->can("{$module}.create"),
            'read' => $user->can("{$module}.read"),
            'update' => $user->can("{$module}.update"),
            'delete' => $user->can("{$module}.delete"),
        ];
    }

    /**
     * Get user's effective permissions organized by module
     */
    public static function getUserPermissionsByModule(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return [];
        }

        $permissions = $user->getAllPermissions();
        $modules = config('modules.modules', []);
        $organized = [];

        foreach ($permissions as $permission) {
            [$module, $action] = explode('.', $permission->name, 2);
            
            if (!isset($organized[$module])) {
                $organized[$module] = [
                    'label' => $modules[$module]['label'] ?? ucfirst($module),
                    'permissions' => []
                ];
            }
            
            $organized[$module]['permissions'][] = [
                'name' => $permission->name,
                'action' => $action,
            ];
        }

        return $organized;
    }

    /**
     * Check if user can access admin areas
     */
    public static function canAccessAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Check if user is a super admin
     */
    public static function isSuperAdmin(): bool
    {
        return self::hasRole('admin');
    }

    /**
     * Check if user can manage other users
     */
    public static function canManageUsers(): bool
    {
        return self::canAny(['users.manage', 'users.create', 'users.update', 'users.delete']);
    }

    /**
     * Generate permission-based navigation menu
     */
    public static function getNavigationItems(): array
    {
        $navigation = [];
        $modules = config('modules.modules', []);
        $groups = config('modules.groups', []);

        foreach ($groups as $groupKey => $group) {
            $groupItems = [];
            
            foreach ($group['modules'] as $moduleKey) {
                if (!isset($modules[$moduleKey])) {
                    continue;
                }
                
                $module = $modules[$moduleKey];
                
                // Check if user has at least read permission for this module
                if (self::can("{$moduleKey}.read") || self::can("{$moduleKey}.access")) {
                    $groupItems[] = [
                        'key' => $moduleKey,
                        'label' => $module['label'],
                        'icon' => $module['icon'],
                        'can_create' => self::can("{$moduleKey}.create"),
                        'can_update' => self::can("{$moduleKey}.update"),
                        'can_delete' => self::can("{$moduleKey}.delete"),
                    ];
                }
            }
            
            if (!empty($groupItems)) {
                $navigation[] = [
                    'group' => $group['label'],
                    'icon' => $group['icon'],
                    'items' => $groupItems
                ];
            }
        }

        return $navigation;
    }

    /**
     * Validate that a permission exists in the system
     */
    public static function permissionExists(string $permission): bool
    {
        return Permission::where('name', $permission)->exists();
    }

    /**
     * Get all available permissions organized by module
     */
    public static function getAllPermissionsGrouped(): array
    {
        $permissions = Permission::all();
        $grouped = [];

        foreach ($permissions as $permission) {
            [$module, $action] = explode('.', $permission->name, 2);
            $grouped[$module][] = [
                'name' => $permission->name,
                'action' => $action,
            ];
        }

        return $grouped;
    }
}