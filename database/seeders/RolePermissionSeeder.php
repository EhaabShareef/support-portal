<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding roles and permissions...');

        // Clear cache to avoid conflicts
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        DB::beginTransaction();
        
        try {
            // Create permissions first
            $this->createPermissions();

            // Create roles and assign permissions
            $this->createRoles();

            DB::commit();
            $this->command->info('✅ Roles and permissions seeded successfully!');
            
        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Error seeding roles and permissions: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createPermissions(): void
    {
        $this->command->info('Creating permissions...');
        
        $permissions = [
            // User management
            'users.create',
            'users.read', 
            'users.update',
            'users.delete',
            'users.manage', // Added missing permission

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
            'tickets.assign', // Added explicit assign permission

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

            // Schedule management
            'schedules.create',
            'schedules.read',
            'schedules.update',
            'schedules.delete',

            // Schedule event types management
            'schedule-event-types.create',
            'schedule-event-types.read',
            'schedule-event-types.update',
            'schedule-event-types.delete',

            // Dashboard access
            'dashboard.access',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
        
        $this->command->info('Created ' . count($permissions) . ' permissions.');
    }

    private function createRoles(): array
    {
        $this->command->info('Creating roles and assigning permissions...');

        // Role descriptions
        $roleDescriptions = [
            'Super Admin' => 'Full system access with all permissions',
            'Admin' => 'Administrative access to manage users, organizations, and all modules',
            'Agent' => 'Support agent with department-based access to tickets and schedules',
            'Client' => 'Client user with basic access to create tickets and view articles',
        ];

        // Super Admin - has ALL permissions
        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web'
        ]);
        $superAdmin->update(['description' => $roleDescriptions['Super Admin']]);
        $superAdmin->syncPermissions(Permission::all());
        $this->command->info('✓ Super Admin role created with all permissions');

        // Admin - organization level admin
        $admin = Role::firstOrCreate([
            'name' => 'Admin', 
            'guard_name' => 'web'
        ]);
        $admin->update(['description' => $roleDescriptions['Admin']]);
        $admin->syncPermissions([
            // User management
            'users.create', 'users.read', 'users.update', 'users.manage',
            
            // Organization management (read and update only)
            'organizations.read', 'organizations.update',
            
            // Department management (full access)
            'departments.create', 'departments.read', 'departments.update', 'departments.delete',
            
            // Ticket management (full access)
            'tickets.create', 'tickets.read', 'tickets.update', 'tickets.delete', 'tickets.assign',
            
            // Contract management (full access)
            'contracts.create', 'contracts.read', 'contracts.update', 'contracts.delete',
            
            // Hardware management (full access)
            'hardware.create', 'hardware.read', 'hardware.update', 'hardware.delete',
            
            // Settings
            'settings.read', 'settings.update',
            
            // Notes and messages
            'notes.create', 'notes.read', 'notes.update', 'notes.delete',
            'messages.create', 'messages.read', 'messages.update', 'messages.delete',
            
            // Articles and reports
            'articles.create', 'articles.read', 'articles.update', 'articles.delete',
            'reports.read',

            // Schedule management (full access)
            'schedules.create', 'schedules.read', 'schedules.update', 'schedules.delete',
            
            // Schedule event types management
            'schedule-event-types.create', 'schedule-event-types.read', 'schedule-event-types.update', 'schedule-event-types.delete',
            
            // Dashboard access
            'dashboard.access',
        ]);
        $this->command->info('✓ Admin role created with administrative permissions');

        // Agent - department level support
        $agent = Role::firstOrCreate([
            'name' => 'Agent',
            'guard_name' => 'web'
        ]);
        $agent->update(['description' => $roleDescriptions['Agent']]);
        $agent->syncPermissions([
            // Basic user access (read only)
            'users.read',
            
            // Organization read access
            'organizations.read',
            
            // Department read access
            'departments.read',
            
            // Ticket management (create, read, update, assign)
            'tickets.create', 'tickets.read', 'tickets.update', 'tickets.assign',
            
            // Contract read access
            'contracts.read',
            
            // Hardware read access
            'hardware.read',
            
            // Notes and messages (create, read, update)
            'notes.create', 'notes.read', 'notes.update',
            'messages.create', 'messages.read', 'messages.update',
            
            // Articles read access
            'articles.read',

            // Schedule read access (agents can view schedules)
            'schedules.read',
            'schedule-event-types.read',
            
            // Dashboard access
            'dashboard.access',
        ]);
        $this->command->info('✓ Agent role created with department-level permissions');

        // Client - basic ticket creation and viewing
        $client = Role::firstOrCreate([
            'name' => 'Client',
            'guard_name' => 'web' 
        ]);
        $client->update(['description' => $roleDescriptions['Client']]);
        $client->syncPermissions([
            // Basic organization access
            'organizations.read',
            
            // Basic ticket access (create and read only)
            'tickets.create', 'tickets.read',
            
            // Basic message access
            'messages.create', 'messages.read',
            
            // Articles read access
            'articles.read',

            // Schedule read access (clients can view schedules)
            'schedules.read',
            'schedule-event-types.read',
            
            // Dashboard access
            'dashboard.access',
        ]);
        $this->command->info('✓ Client role created with basic permissions');

        return [
            'super_admin' => $superAdmin,
            'admin' => $admin,
            'agent' => $agent,
            'client' => $client,
        ];
    }
}