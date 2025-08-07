<?php

/**
 * Access Test Configuration
 * 
 * This configuration file defines the navigation elements, routes, and permissions
 * that should be tested for role-based access control.
 * 
 * Update this file when adding new features, routes, or navigation elements
 * to ensure they are included in the access tests.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation Elements to Test
    |--------------------------------------------------------------------------
    |
    | Define key navigation elements that should be tested for role-based visibility.
    | Each element should specify the role requirements and description.
    |
    */
    
    'navigation_elements' => [
        [
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'role_requirement' => null, // All authenticated users
            'permission_requirement' => null,
            'description' => 'Main dashboard page',
            'critical' => true,
        ],
        [
            'name' => 'Users Management',
            'route' => 'admin.users.index',
            'role_requirement' => 'admin',
            'permission_requirement' => 'users.read',
            'description' => 'Admin user management interface',
            'critical' => true,
        ],
        [
            'name' => 'Roles Management', 
            'route' => 'admin.roles.index',
            'role_requirement' => 'admin',
            'permission_requirement' => 'users.manage',
            'description' => 'Role and permission management',
            'critical' => true,
        ],
        [
            'name' => 'Organizations',
            'route' => 'organizations.index',
            'role_requirement' => null,
            'permission_requirement' => 'organizations.read',
            'description' => 'Organization listing and management',
            'critical' => true,
        ],
        [
            'name' => 'Tickets',
            'route' => 'tickets.index',
            'role_requirement' => null,
            'permission_requirement' => 'tickets.read',
            'description' => 'Ticket management system',
            'critical' => true,
        ],
        [
            'name' => 'Create Ticket',
            'route' => 'tickets.create',
            'role_requirement' => null,
            'permission_requirement' => 'tickets.create',
            'description' => 'Ticket creation form',
            'critical' => true,
        ],
        [
            'name' => 'Schedule Calendar',
            'route' => 'schedule.index',
            'role_requirement' => 'admin|client',
            'permission_requirement' => 'schedules.read',
            'description' => 'Team schedule calendar',
            'critical' => false,
        ],
        [
            'name' => 'System Settings',
            'route' => 'admin.settings',
            'role_requirement' => 'admin',
            'permission_requirement' => 'settings.read',
            'description' => 'System configuration settings',
            'critical' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Key Permissions to Test
    |--------------------------------------------------------------------------
    |
    | Define the most important permissions that should be tested across roles.
    | Group them by module for better organization.
    |
    */
    
    'key_permissions' => [
        'users' => [
            'users.create' => 'Create new users',
            'users.read' => 'View user information',
            'users.update' => 'Update user details',
            'users.delete' => 'Delete users',
            'users.manage' => 'Full user management access',
        ],
        'organizations' => [
            'organizations.create' => 'Create organizations',
            'organizations.read' => 'View organizations',
            'organizations.update' => 'Update organizations',
            'organizations.delete' => 'Delete organizations',
        ],
        'tickets' => [
            'tickets.create' => 'Create support tickets',
            'tickets.read' => 'View tickets',
            'tickets.update' => 'Update ticket information',
            'tickets.delete' => 'Delete tickets',
            'tickets.assign' => 'Assign tickets to agents',
        ],
        'departments' => [
            'departments.create' => 'Create departments',
            'departments.read' => 'View departments',
            'departments.update' => 'Update departments',
            'departments.delete' => 'Delete departments',
        ],
        'schedules' => [
            'schedules.create' => 'Create schedule entries',
            'schedules.read' => 'View schedules',
            'schedules.update' => 'Update schedules',
            'schedules.delete' => 'Delete schedules',
        ],
        'contracts' => [
            'contracts.create' => 'Create contracts',
            'contracts.read' => 'View contracts',
            'contracts.update' => 'Update contracts',
            'contracts.delete' => 'Delete contracts',
        ],
        'hardware' => [
            'hardware.create' => 'Add hardware records',
            'hardware.read' => 'View hardware information',
            'hardware.update' => 'Update hardware records',
            'hardware.delete' => 'Delete hardware records',
        ],
        'settings' => [
            'settings.read' => 'View system settings',
            'settings.update' => 'Modify system settings',
        ],
        'articles' => [
            'articles.create' => 'Create knowledge articles',
            'articles.read' => 'View knowledge articles',
            'articles.update' => 'Update articles',
            'articles.delete' => 'Delete articles',
        ],
        'reports' => [
            'reports.read' => 'View system reports',
        ],
        'system' => [
            'dashboard.access' => 'Access main dashboard',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Expected Role Capabilities
    |--------------------------------------------------------------------------
    |
    | Define what each role should be able to access. This helps validate
    | that roles have the correct permissions assigned.
    |
    */
    
    'role_expectations' => [
        'admin' => [
            'description' => 'Full system administrator with complete access',
            'should_access' => [
                'all_navigation_elements' => true,
                'all_routes' => true,
                'user_management' => true,
                'role_management' => true,
                'system_settings' => true,
                'all_modules' => true,
            ],
            'permission_count' => 50, // Expected total permissions
        ],
        'support' => [
            'description' => 'Support staff with operational access',
            'should_access' => [
                'tickets' => true,
                'organizations' => true,
                'schedules' => true,
                'user_management' => false,
                'role_management' => false,
                'system_settings' => false,
                'delete_operations' => false,
            ],
            'permission_count' => 36, // Expected total permissions
        ],
        'client' => [
            'description' => 'External client with limited access',
            'should_access' => [
                'own_tickets' => true,
                'create_tickets' => true,
                'knowledge_articles' => true,
                'user_management' => false,
                'organizations_management' => false,
                'system_settings' => false,
                'other_clients_data' => false,
            ],
            'permission_count' => 5, // Expected total permissions
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Test Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the test runner.
    |
    */
    
    'test_config' => [
        'create_test_users' => false, // Use existing users instead of creating test ones
        'cleanup_after_test' => false, // Don't delete test users
        'save_detailed_report' => true, // Save JSON report to storage/logs
        'show_passed_tests' => true, // Show successful tests in output
        'show_permission_details' => false, // Show individual permission checks
        'color_output' => true, // Use colored console output
    ],

    /*
    |--------------------------------------------------------------------------
    | Blade Files to Parse
    |--------------------------------------------------------------------------
    |
    | Blade template files that should be parsed for role/permission directives.
    | Add new navigation or layout files here.
    |
    */
    
    'blade_files' => [
        'resources/views/components/sidebar.blade.php',
        'resources/views/components/navigation.blade.php',
        'resources/views/layouts/app.blade.php',
        // Add new navigation files here when created
    ],

    /*
    |--------------------------------------------------------------------------
    | Critical Routes
    |--------------------------------------------------------------------------
    |
    | Routes that are considered critical for system functionality.
    | These will be highlighted in test reports if access issues are found.
    |
    */
    
    'critical_routes' => [
        'dashboard',
        'admin.users.index',
        'admin.roles.index',
        'organizations.index',
        'tickets.index',
        'tickets.create',
    ],

];