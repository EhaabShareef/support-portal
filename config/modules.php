<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Modules Configuration
    |--------------------------------------------------------------------------
    |
    | This file defines all modules and their available actions for the 
    | permission system. This serves as a single source of truth for
    | generating permissions, seeding roles, and building admin interfaces.
    |
    | Each module can have standard CRUD actions plus custom actions.
    | Permissions are generated in the format: <module>.<action>
    |
    */

    'modules' => [
        'users' => [
            'label' => 'User Management',
            'description' => 'Manage system users',
            'actions' => ['create', 'read', 'update', 'delete', 'manage'],
            'icon' => 'heroicon-o-users',
        ],
        
        'organizations' => [
            'label' => 'Organizations',
            'description' => 'Manage client organizations',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-building-office',
        ],
        
        'departments' => [
            'label' => 'Departments',
            'description' => 'Manage internal departments',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-building-office-2',
        ],
        
        'tickets' => [
            'label' => 'Tickets',
            'description' => 'Support ticket management',
            'actions' => ['create', 'read', 'update', 'delete', 'assign'],
            'icon' => 'heroicon-o-ticket',
        ],
        
        'contracts' => [
            'label' => 'Contracts',
            'description' => 'Client contract management',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-document-text',
        ],
        
        'hardware' => [
            'label' => 'Hardware',
            'description' => 'Hardware asset management',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-computer-desktop',
        ],
        
        'settings' => [
            'label' => 'System Settings',
            'description' => 'Application configuration',
            'actions' => ['read', 'update'],
            'icon' => 'heroicon-o-cog-6-tooth',
        ],
        
        'notes' => [
            'label' => 'Notes',
            'description' => 'Internal notes management',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-document-text',
        ],
        
        'messages' => [
            'label' => 'Messages',
            'description' => 'Ticket messages and communication',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-chat-bubble-left-right',
        ],
        
        'reports' => [
            'label' => 'Reports',
            'description' => 'System reports and analytics',
            'actions' => ['read'],
            'icon' => 'heroicon-o-chart-bar',
        ],
        
        'articles' => [
            'label' => 'Knowledge Base',
            'description' => 'Knowledge base articles',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-book-open',
        ],
        
        'schedules' => [
            'label' => 'Schedules',
            'description' => 'Schedule and calendar management',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-calendar',
        ],
        
        'schedule-event-types' => [
            'label' => 'Schedule Event Types',
            'description' => 'Manage types of scheduled events',
            'actions' => ['create', 'read', 'update', 'delete'],
            'icon' => 'heroicon-o-tag',
        ],
        
        'dashboard' => [
            'label' => 'Dashboard',
            'description' => 'Dashboard access and viewing',
            'actions' => ['access'],
            'icon' => 'heroicon-o-squares-2x2',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Groups
    |--------------------------------------------------------------------------
    |
    | Group related modules together for better organization in the UI.
    | Each group can have its own icon and description.
    |
    */

    'groups' => [
        'core' => [
            'label' => 'Core Management',
            'description' => 'Essential system management features',
            'icon' => 'heroicon-o-squares-2x2',
            'modules' => ['users', 'organizations', 'departments', 'settings'],
        ],
        
        'support' => [
            'label' => 'Support Operations',
            'description' => 'Customer support and ticketing',
            'icon' => 'heroicon-o-lifebuoy',
            'modules' => ['tickets', 'notes', 'messages', 'articles'],
        ],
        
        'business' => [
            'label' => 'Business Management',
            'description' => 'Contracts and asset management',
            'icon' => 'heroicon-o-briefcase',
            'modules' => ['contracts', 'hardware'],
        ],
        
        'planning' => [
            'label' => 'Planning & Scheduling',
            'description' => 'Schedule and event management',
            'icon' => 'heroicon-o-calendar-days',
            'modules' => ['schedules', 'schedule-event-types'],
        ],
        
        'analytics' => [
            'label' => 'Analytics',
            'description' => 'Reports and data insights',
            'icon' => 'heroicon-o-chart-bar-square',
            'modules' => ['reports', 'dashboard'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Labels
    |--------------------------------------------------------------------------
    |
    | Human-readable labels for actions used in UI components.
    |
    */

    'action_labels' => [
        'create' => 'Create',
        'read' => 'View',
        'update' => 'Edit',
        'delete' => 'Delete',
        'manage' => 'Manage',
        'assign' => 'Assign',
        'access' => 'Access',
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Templates
    |--------------------------------------------------------------------------
    |
    | Pre-defined permission sets for common roles.
    | These are used when seeding roles and permissions.
    |
    */

    'role_templates' => [
        'Super Admin' => [
            'description' => 'Full system access with all permissions',
            'permissions' => '*', // All permissions
        ],
        
        'Admin' => [
            'description' => 'Administrative access to manage users, organizations, and all modules',
            'permissions' => [
                'users.*',
                'organizations.read', 'organizations.update',
                'departments.*',
                'tickets.*',
                'contracts.*',
                'hardware.*',
                'settings.*',
                'notes.*',
                'messages.*',
                'articles.*',
                'reports.read',
                'schedules.*',
                'schedule-event-types.*',
                'dashboard.access',
            ],
        ],
        
        'Agent' => [
            'description' => 'Support agent with department-based access to tickets and schedules',
            'permissions' => [
                'users.read',
                'organizations.read',
                'departments.read',
                'tickets.create', 'tickets.read', 'tickets.update', 'tickets.assign',
                'contracts.read',
                'hardware.read',
                'notes.create', 'notes.read', 'notes.update',
                'messages.create', 'messages.read', 'messages.update',
                'articles.read',
                'schedules.read',
                'schedule-event-types.read',
                'dashboard.access',
            ],
        ],
        
        'Client' => [
            'description' => 'Client user with basic access to create tickets and view articles',
            'permissions' => [
                'organizations.read',
                'tickets.create', 'tickets.read',
                'messages.create', 'messages.read',
                'articles.read',
                'schedules.read',
                'schedule-event-types.read',
                'dashboard.access',
            ],
        ],
    ],
];