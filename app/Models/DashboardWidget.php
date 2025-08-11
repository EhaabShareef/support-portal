<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'base_component',
        'available_sizes',
        'default_size',
        'sort_order',
        'is_active',
        'is_default_visible',
        'permissions',
        'options',
    ];

    protected $casts = [
        'available_sizes' => 'array',
        'permissions' => 'array',
        'options' => 'array',
        'is_active' => 'boolean',
        'is_default_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the user widget settings for this widget
     */
    public function userSettings(): HasMany
    {
        return $this->hasMany(UserWidgetSetting::class, 'widget_id');
    }

    /**
     * Get user settings for a specific user
     */
    public function getSettingsForUser(User $user): ?UserWidgetSetting
    {
        return $this->userSettings()->where('user_id', $user->id)->first();
    }

    /**
     * Check if user has permission to access this widget (for customize modal)
     */
    public function isAccessibleForUser(User $user): bool
    {
        // Check if widget is active
        if (!$this->is_active) {
            return false;
        }

        // Check role-based category access
        $userRole = $user->roles->first()?->name ?? 'client';
        if ($this->category !== $userRole) {
            return false;
        }

        // Check permissions if specified
        if ($this->permissions) {
            foreach ($this->permissions as $permission) {
                try {
                    if (!$user->can($permission)) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if widget should be visible for a user (for dashboard display)
     */
    public function isVisibleForUser(User $user): bool
    {
        // First check if user has access
        if (!$this->isAccessibleForUser($user)) {
            return false;
        }

        // Check user settings
        $userSetting = $this->getSettingsForUser($user);
        if ($userSetting) {
            return (bool) $userSetting->is_visible;
        }

        // Default to widget's default visibility
        return $this->is_default_visible;
    }

    /**
     * Get effective size for user (user setting or default)
     */
    public function getSizeForUser(User $user): string
    {
        $userSetting = $this->getSettingsForUser($user);
        $selectedSize = $userSetting?->size ?? $this->default_size;
        
        // Ensure selected size is available for this widget
        if (!in_array($selectedSize, $this->available_sizes)) {
            return $this->default_size;
        }
        
        return $selectedSize;
    }

    /**
     * Get effective order for user (user setting or default)
     */
    public function getOrderForUser(User $user): int
    {
        $userSetting = $this->getSettingsForUser($user);
        return $userSetting?->sort_order ?? $this->sort_order;
    }

    /**
     * Get effective options for user (merged widget and user options)
     */
    public function getOptionsForUser(User $user): array
    {
        $widgetOptions = $this->options ?? [];
        $userSetting = $this->getSettingsForUser($user);
        $userOptions = $userSetting?->options ?? [];

        return array_merge($widgetOptions, $userOptions);
    }

    /**
     * Get the component name for a specific size
     */
    public function getComponentForSize(string $size): string
    {
        // Ensure size is available
        if (!in_array($size, $this->available_sizes)) {
            $size = $this->default_size;
        }

        // Convert size to component suffix (e.g., '2x2' -> 'medium')
        $sizeMap = [
            '1x1' => 'small',
            '2x1' => 'wide', 
            '2x2' => 'medium',
            '3x2' => 'large',
            '3x3' => 'xlarge',
        ];

        $sizeSuffix = $sizeMap[$size] ?? 'medium';
        
        return "dashboard.widgets.{$this->base_component}.{$sizeSuffix}";
    }

    /**
     * Check if the component exists for a specific size
     */
    public function componentExists(string $size): bool
    {
        $componentName = $this->getComponentForSize($size);
        
        // Convert component name to class path with proper case conversion
        $parts = explode('.', $componentName);
        $className = 'App\\Livewire\\' . implode('\\', array_map(function($part) {
            // Convert kebab-case to PascalCase (e.g., system-health -> SystemHealth)
            return str_replace(' ', '', ucwords(str_replace('-', ' ', $part)));
        }, $parts));
        
        return class_exists($className);
    }

    /**
     * Get the component name for a size, with fallback if not found
     */
    public function getComponentForSizeWithFallback(string $size): array
    {
        $componentName = $this->getComponentForSize($size);
        
        if ($this->componentExists($size)) {
            return [
                'component' => $componentName,
                'is_fallback' => false,
                'params' => []
            ];
        }
        
        // Return fallback component with parameters
        return [
            'component' => 'dashboard.widgets.fallback-widget',
            'is_fallback' => true,
            'params' => [
                'widgetName' => $this->name,
                'widgetSize' => $size
            ]
        ];
    }

    /**
     * Scope to get active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category (role)
     */
    public function scopeForRole($query, string $role)
    {
        return $query->where('category', $role);
    }

    /**
     * Scope to order by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}