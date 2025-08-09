<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard Widget System
    |--------------------------------------------------------------------------
    |
    | Configuration options for the dashboard widget system
    |
    */

    /**
     * Enable the new widget-based dashboard system
     */
    'widgets_enabled' => env('DASHBOARD_WIDGETS_ENABLED', false),

    /**
     * Cache configuration for dashboard data
     */
    'cache' => [
        'ttl' => env('DASHBOARD_CACHE_TTL', 300), // 5 minutes
        'prefix' => 'dashboard_',
    ],

    /**
     * Available widget sizes and their corresponding CSS classes
     */
    'sizes' => [
        '1x1' => [
            'label' => 'Small',
            'classes' => 'col-span-1 row-span-1',
            'description' => '1 column × 1 row',
        ],
        '2x1' => [
            'label' => 'Wide',
            'classes' => 'col-span-1 md:col-span-2 row-span-1',
            'description' => '2 columns × 1 row',
        ],
        '2x2' => [
            'label' => 'Medium',
            'classes' => 'col-span-1 md:col-span-2 row-span-2',
            'description' => '2 columns × 2 rows',
        ],
        '3x2' => [
            'label' => 'Large',
            'classes' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-2',
            'description' => '3 columns × 2 rows',
        ],
        '3x3' => [
            'label' => 'Extra Large',
            'classes' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-3',
            'description' => '3 columns × 3 rows',
        ],
    ],

    /**
     * Grid system configuration
     */
    'grid' => [
        'columns' => [
            'mobile' => 1,
            'tablet' => 2,
            'desktop' => 4,
        ],
        'gap' => 'gap-6',
        'auto_rows' => 'auto-rows-min',
    ],

    /**
     * Performance settings
     */
    'performance' => [
        'lazy_load' => true,
        'skeleton_timeout' => 2000, // ms
        'refresh_interval' => 30000, // 30 seconds
    ],

    /**
     * Default widget permissions by role
     */
    'role_permissions' => [
        'admin' => [
            'dashboard.admin',
            'contracts.read',
            'hardware.read',
            'tickets.read',
            'organizations.read',
            'departments.read',
        ],
        'support' => [
            'dashboard.support',
            'tickets.read',
            'tickets.update',
            'departments.read',
        ],
        'client' => [
            'dashboard.client',
            'tickets.read',
            'tickets.create',
            'contracts.read',
        ],
    ],

    /**
     * Widget refresh events
     */
    'events' => [
        'refresh_all' => 'refreshAllWidgets',
        'refresh_single' => 'refreshWidget',
    ],
];