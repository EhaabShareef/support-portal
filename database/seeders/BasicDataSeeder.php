<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\DepartmentGroup;
use App\Models\Department;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding basic data...');

        // Get existing roles (should be created by RolePermissionSeeder)
        $roles = $this->getRoles();

        // Create sample organization
        $organization = $this->createOrganization();

        // Create department groups
        $departmentGroups = $this->createDepartmentGroups();

        // Create departments
        $departments = $this->createDepartments($departmentGroups);

        $this->command->info('✅ Basic data seeded successfully!');
    }

    private function getRoles(): array
    {
        // Get existing roles (these should already be created by RolePermissionSeeder)
        return [
            'admin' => Role::where('name', 'admin')->firstOrFail(),
            'support' => Role::where('name', 'support')->firstOrFail(),
            'client' => Role::where('name', 'client')->firstOrFail(),
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
                'name' => 'Admin',
                'description' => 'System Administration',
                'email' => 'admin@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 1,
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales Engagement',
                'email' => 'sales@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 2,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance Department',
                'email' => 'finance@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 3,
            ],
            [
                'name' => 'Resource',
                'description' => 'Human Resource',
                'email' => 'resource@htm.com.mv',
                'department_group_id' => $departmentGroups[0]->id, // Admin
                'sort_order' => 4,
            ],
        ];


        $createdDepartments = [];
        foreach ($departments as $department) {
            $createdDepartments[] = Department::firstOrCreate($department);
        }

        return $createdDepartments;
    }

}
