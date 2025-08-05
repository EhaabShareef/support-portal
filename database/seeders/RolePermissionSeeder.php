<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding roles and permissions...');

        // Create permissions
        $this->createPermissions();

        // Create/update roles
        $this->createRoles();

        $this->command->info('✅ Roles and permissions seeded successfully!');
    }

    private function createPermissions(): void
    {
        $permissions = [
            // User management
            'users.create',
            'users.read',
            'users.update',
            'users.delete',

            // Organization management
            'organizations.create',
            'organizations.read',
            'organizations.update',
            'organizations.delete',

            // Department management
            'departments.create',
            'departments.read',
            'departments.update',
            'departments.delete',

            // Ticket management
            'tickets.create',
            'tickets.read',
            'tickets.update',
            'tickets.delete',

            // Contract management
            'contracts.create',
            'contracts.read',
            'contracts.update',
            'contracts.delete',

            // Hardware management
            'hardware.create',
            'hardware.read',
            'hardware.update',
            'hardware.delete',

            // Settings management
            'settings.read',
            'settings.update',

            // Note management
            'notes.create',
            'notes.read',
            'notes.update',
            'notes.delete',

            // Message management
            'messages.create',
            'messages.read',
            'messages.update',
            'messages.delete',

            // Reports
            'reports.read',

            // Knowledge base (articles)
            'articles.create',
            'articles.read',
            'articles.update',
            'articles.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function createRoles(): array
    {
        // Create role descriptions
        $roleDescriptions = [
            'Super Admin' => 'Full system access with all permissions',
            'Admin' => 'Administrative access to manage users, organizations, and all modules',
            'Agent' => 'Support agent with limited access to tickets and basic operations within their department',
            'Client' => 'Client user with basic access to create and view tickets and articles',
        ];

        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin', 
            'guard_name' => 'web'
        ]);
        $superAdmin->update(['description' => $roleDescriptions['Super Admin']]);
        $superAdmin->syncPermissions(Permission::all());

        // Admin - organization level admin
        $admin = Role::firstOrCreate([
            'name' => 'Admin', 
            'guard_name' => 'web'
        ]);
        $admin->update(['description' => $roleDescriptions['Admin']]);
        $admin->syncPermissions([
            // User management
            'users.create', 'users.read', 'users.update',
            
            // Organization management
            'organizations.read', 'organizations.update',
            
            // Department management
            'departments.create', 'departments.read', 'departments.update', 'departments.delete',
            
            // Ticket management
            'tickets.create', 'tickets.read', 'tickets.update', 'tickets.delete',
            
            // Contract management
            'contracts.create', 'contracts.read', 'contracts.update', 'contracts.delete',
            
            // Hardware management
            'hardware.create', 'hardware.read', 'hardware.update', 'hardware.delete',
            
            // Settings
            'settings.read', 'settings.update',
            
            // Notes and messages
            'notes.create', 'notes.read', 'notes.update', 'notes.delete',
            'messages.create', 'messages.read', 'messages.update', 'messages.delete',
            
            // Articles and reports
            'articles.create', 'articles.read', 'articles.update', 'articles.delete',
            'reports.read',
        ]);

        // Agent - department level support
        $agent = Role::firstOrCreate([
            'name' => 'Agent', 
            'guard_name' => 'web'
        ]);
        $agent->update(['description' => $roleDescriptions['Agent']]);
        $agent->syncPermissions([
            // Basic user access
            'users.read',
            
            // Organization read access
            'organizations.read',
            
            // Department read access
            'departments.read',
            
            // Ticket management (limited to department)
            'tickets.create', 'tickets.read', 'tickets.update',
            
            // Contract read access
            'contracts.read',
            
            // Hardware read access
            'hardware.read',
            
            // Notes and messages
            'notes.create', 'notes.read', 'notes.update',
            'messages.create', 'messages.read', 'messages.update',
            
            // Articles read access
            'articles.read',
        ]);

        // Client - can create and view own tickets
        $client = Role::firstOrCreate([
            'name' => 'Client', 
            'guard_name' => 'web'
        ]);
        $client->update(['description' => $roleDescriptions['Client']]);
        $client->syncPermissions([
            // Basic organization access
            'organizations.read',
            
            // Basic ticket access
            'tickets.create', 'tickets.read',
            
            // Basic message access
            'messages.create', 'messages.read',
            
            // Articles read access
            'articles.read',
        ]);

        return [
            'super_admin' => $superAdmin,
            'admin' => $admin,
            'agent' => $agent,
            'client' => $client,
        ];
    }
}