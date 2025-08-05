<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\DepartmentGroup;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding basic data...');

        // Create permissions
        $this->createPermissions();

        // Create roles
        $roles = $this->createRoles();

        // Create sample organization
        $organization = $this->createOrganization();

        // Create department groups
        $departmentGroups = $this->createDepartmentGroups();

        // Create departments
        $departments = $this->createDepartments($departmentGroups);

        // Create users
        $this->createUsers($organization, $departments, $roles);

        $this->command->info('✅ Basic data seeded successfully!');
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
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - organization level admin
        $admin = Role::firstOrCreate([
            'name' => 'Admin', 
            'guard_name' => 'web'
        ]);
        $admin->update(['description' => $roleDescriptions['Admin']]);
        $admin->givePermissionTo([
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
        $agent->givePermissionTo([
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
        $client->givePermissionTo([
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

    private function createOrganization(): Organization
    {
        return Organization::firstOrCreate([
            'name' => 'Hospitality Technology',
            'company' => 'Hospitality Technology Ltd',
            'company_contact' => 'Ismail Ibrahim',
            'tin_no' => '123456789',
            'email' => 'info@ht.com',
            'phone' => '+1234567890',
            'is_active' => true,
            'subscription_status' => 'active',
            'notes' => 'Default Organization',
        ]);
    }

    private function createDepartmentGroups(): array
    {
        $groups = [
            [
                'name' => 'Admin',
                'description' => 'Adminstrative Group',
                'color' => '#3b82f6', // blue
                'sort_order' => 1,
            ],
            [
                'name' => 'PMS',
                'description' => 'Property Management System Softwares',
                'color' => '#10b981', // green
                'sort_order' => 2,
            ],
            [
                'name' => 'POS',
                'description' => 'Point of Sales Softwares',
                'color' => '#f59e0b', // amber
                'sort_order' => 3,
            ],
            [
                'name' => 'MC',
                'description' => 'Materials Control',
                'color' => '#8b5cf6', // violet
                'sort_order' => 4,
            ],
            [
                'name' => 'Hardware',
                'description' => 'Hardware Group',
                'color' => '#ef4444', // red
                'sort_order' => 5,
            ],
        ];

        $departmentGroups = [];
        foreach ($groups as $group) {
            $departmentGroups[] = DepartmentGroup::firstOrCreate($group);
        }

        return $departmentGroups;
    }

    private function createDepartments(array $departmentGroups): array
    {
        $departments = [
            [
                'name' => 'OPERA',
                'description' => 'Property Management On premise',
                'email' => 'opera@htm.com.mv',
                'department_group_id' => $departmentGroups[1]->id, // PMS
                'sort_order' => 1,
            ],
            [
                'name' => 'OPERA Cloud',
                'description' => 'Property Management Cloud System',
                'email' => 'operacloud@htm.com.mv',
                'department_group_id' => $departmentGroups[1]->id, // PMS
                'sort_order' => 2,
            ],
            [
                'name' => 'OXI',
                'description' => 'Opera Exchange Interface',
                'email' => 'oxi@htm.com.mv',
                'department_group_id' => $departmentGroups[1]->id, // PMS
                'sort_order' => 3,
            ],
            [
                'name' => 'IFC',
                'description' => 'Opera Interface',
                'email' => 'ifc@htm.com.mv',
                'department_group_id' => $departmentGroups[1]->id, // PMS
                'sort_order' => 4,
            ],
            [
                'name' => 'VISION',
                'description' => 'Infor Query and Analysis',
                'email' => 'vision@htm.com.mv',
                'department_group_id' => $departmentGroups[1]->id, // PMS
                'sort_order' => 5,
            ],
            [
                'name' => 'SYMPHONY',
                'description' => 'Point of Sales - Symphony On-premise',
                'email' => 'symphony@htm.com.mv',
                'department_group_id' => $departmentGroups[2]->id, // POS
                'sort_order' => 1,
            ],
            [
                'name' => 'RES9700',
                'description' => 'Point of Sales - 9700',
                'email' => 'res9700@htm.com.mv',
                'department_group_id' => $departmentGroups[2]->id, // POS
                'sort_order' => 2,
            ],
            [
                'name' => 'SYMPHONY CLOUD',
                'description' => 'Point of Sales - Symphony Cloud',
                'email' => 'symphonycloud@htm.com.mv',
                'department_group_id' => $departmentGroups[2]->id, // POS
                'sort_order' => 3,
            ],
            [
                'name' => 'MC',
                'description' => 'Materials Control on-premise',
                'email' => 'mc@htm.com.mv',
                'department_group_id' => $departmentGroups[3]->id, // MC
                'sort_order' => 1,
            ],
            [
                'name' => 'Hardware',
                'description' => 'Hardware',
                'email' => 'hardware@htm.com.mv',
                'department_group_id' => $departmentGroups[4]->id, // Hardware
                'sort_order' => 1,
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales Engagement',
                'email' => 'sales@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 1,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance Department',
                'email' => 'finance@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 2,
            ],
            [
                'name' => 'Resource',
                'description' => 'Human Resource',
                'email' => 'resource@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 3,
            ],
        ];


        $createdDepartments = [];
        foreach ($departments as $department) {
            $createdDepartments[] = Department::firstOrCreate($department);
        }

        return $createdDepartments;
    }

    private function createUsers(Organization $organization, array $departments, array $roles): void
    {
        // Super Admin - assign to IT Support department
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@htm.com',
        ], [
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'department_id' => $departments[0]->id, // IT Support
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        // Assign roles without team context (teams disabled)
        $superAdmin->assignRole($roles['super_admin']);

        // IT Admin
        $admin = User::firstOrCreate([
            'email' => 'admin@ht.com',
        ], [
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'department_id' => $departments[0]->id, // IT Support
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $admin->assignRole($roles['admin']);

        // IT Agent
        $agent = User::firstOrCreate([
            'email' => 'agent@ht.com',
        ], [
            'name' => 'Support Agent',
            'username' => 'agent',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'department_id' => $departments[0]->id, // IT Support
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $agent->assignRole($roles['agent']);

        // Sample Client - assign to Customer Support department
        $client = User::firstOrCreate([
            'email' => 'client@ht.com',
        ], [
            'name' => 'Client User',
            'username' => 'client',
            'password' => Hash::make('password'),
            'organization_id' => $organization->id,
            'department_id' => $departments[3]->id, // Customer Support
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $client->assignRole($roles['client']);
    }
}
