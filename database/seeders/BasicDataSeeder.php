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
            'users.view', 'users.create', 'users.edit', 'users.delete',
            
            // Organization management
            'organizations.view', 'organizations.edit',
            
            // Department management
            'departments.view', 'departments.create', 'departments.edit', 'departments.delete',
            
            // Ticket management
            'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.delete',
            'tickets.assign', 'tickets.close',
            
            // Knowledge base
            'articles.view', 'articles.create', 'articles.edit', 'articles.delete',
            
            // Reports
            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function createRoles(): array
    {
        // For team-based permissions, create roles without team first, then assign with team context
        
        // Super Admin - has all permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin - organization level admin
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'users.view', 'users.create', 'users.edit',
            'organizations.view',
            'departments.view', 'departments.create', 'departments.edit',
            'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.assign', 'tickets.close',
            'articles.view', 'articles.create', 'articles.edit',
            'reports.view',
        ]);

        // Agent - department level support
        $agent = Role::firstOrCreate(['name' => 'Agent', 'guard_name' => 'web']);
        $agent->givePermissionTo([
            'tickets.view', 'tickets.create', 'tickets.edit', 'tickets.close',
            'articles.view',
            'users.view',
        ]);

        // Client - can create and view own tickets
        $client = Role::firstOrCreate(['name' => 'Client', 'guard_name' => 'web']);
        $client->givePermissionTo([
            'tickets.view', 'tickets.create',
            'articles.view',
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
                'color' => '#3b82f6',
                'sort_order' => 1,
            ],
            [
                'name' => 'PMS',
                'description' => 'Property Management System Softwares',
                'color' => '#10b981',
                'sort_order' => 2,
            ],
            [
                'name' => 'POS',
                'description' => 'Point of Sales Softwares',
                'color' => '#f59e0b',
                'sort_order' => 3,
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
                'name' => 'IT Support',
                'description' => 'Technical support and system administration',
                'email' => 'it@pms.com',
                'department_group_id' => $departmentGroups[0]->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'Network Administration',
                'description' => 'Network infrastructure and security',
                'email' => 'network@ht.com',
                'department_group_id' => $departmentGroups[0]->id,
                'sort_order' => 2,
            ],
            [
                'name' => 'Human Resources',
                'description' => 'Employee management and support',
                'email' => 'hr@ht.com',
                'department_group_id' => $departmentGroups[1]->id,
                'sort_order' => 1,
            ],
            [
                'name' => 'Customer Support',
                'description' => 'Customer service and support',
                'email' => 'support@testcompany.com',
                'department_group_id' => $departmentGroups[2]->id,
                'sort_order' => 1,
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
            'email' => 'superadmin@samplecompany.com',
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