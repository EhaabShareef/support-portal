<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentGroup;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏬 Seeding departments...');

        // Clear existing departments
        Department::query()->delete();

        // Get department groups
        $adminGroup = DepartmentGroup::where('name', 'Admin')->first();
        $pmsGroup = DepartmentGroup::where('name', 'PMS')->first();
        $posGroup = DepartmentGroup::where('name', 'POS')->first();
        $mcGroup = DepartmentGroup::where('name', 'MC')->first();
        $boGroup = DepartmentGroup::where('name', 'BO')->first();
        $hardwareGroup = DepartmentGroup::where('name', 'Hardware')->first();
        $emailGroup = DepartmentGroup::where('name', 'Email')->first();

        $departments = [
            // Admin Group
            [
                'name' => 'Super Admin',
                'description' => 'System Super Administration',
                'department_group_id' => $adminGroup->id,
                'email' => 'superadmin@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Finance',
                'description' => 'Finance Department',
                'department_group_id' => $adminGroup->id,
                'email' => 'finance@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Human Resource',
                'description' => 'Human Resource Department',
                'department_group_id' => $adminGroup->id,
                'email' => 'hr@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Project Manage',
                'description' => 'Project Management Department',
                'department_group_id' => $adminGroup->id,
                'email' => 'pm@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Sales',
                'description' => 'Sales Department',
                'department_group_id' => $adminGroup->id,
                'email' => 'sales@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 5,
            ],

            // PMS Group
            [
                'name' => 'Opera',
                'description' => 'Oracle Opera PMS',
                'department_group_id' => $pmsGroup->id,
                'email' => 'opera@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Opera Cloud',
                'description' => 'Oracle Opera Cloud PMS',
                'department_group_id' => $pmsGroup->id,
                'email' => 'operacloud@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Vision',
                'description' => 'Vision Analytics & Reporting',
                'department_group_id' => $pmsGroup->id,
                'email' => 'vision@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'R&A',
                'description' => 'Reporting & Analytics (PMS)',
                'department_group_id' => $pmsGroup->id,
                'email' => 'pms-ra@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'OXI',
                'description' => 'Opera Exchange Interface',
                'department_group_id' => $pmsGroup->id,
                'email' => 'oxi@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Technical',
                'description' => 'PMS Technical Support',
                'department_group_id' => $pmsGroup->id,
                'email' => 'pms-tech@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 6,
            ],

            // POS Group
            [
                'name' => 'Simphny',
                'description' => 'Oracle Simphony POS',
                'department_group_id' => $posGroup->id,
                'email' => 'simphony@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Simphony Cloud',
                'description' => 'Oracle Simphony Cloud POS',
                'department_group_id' => $posGroup->id,
                'email' => 'simphonycloud@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'RES 3700',
                'description' => 'Micros RES 3700 POS',
                'department_group_id' => $posGroup->id,
                'email' => 'res3700@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'RES 9700',
                'description' => 'Micros RES 9700 POS',
                'department_group_id' => $posGroup->id,
                'email' => 'res9700@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'R&A',
                'description' => 'Reporting & Analytics (POS)',
                'department_group_id' => $posGroup->id,
                'email' => 'pos-ra@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 5,
            ],

            // MC Group
            [
                'name' => 'Materials Control',
                'description' => 'Materials Control System',
                'department_group_id' => $mcGroup->id,
                'email' => 'mc@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Reporting',
                'description' => 'MC Reporting & Analytics',
                'department_group_id' => $mcGroup->id,
                'email' => 'mc-reporting@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 2,
            ],

            // BO Group
            [
                'name' => 'BackOffice',
                'description' => 'BackOffice Systems',
                'department_group_id' => $boGroup->id,
                'email' => 'backoffice@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],

            // Hardware Group
            [
                'name' => 'Local',
                'description' => 'Local Hardware Support',
                'department_group_id' => $hardwareGroup->id,
                'email' => 'hardware-local@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Oracle',
                'description' => 'Oracle Hardware Support',
                'department_group_id' => $hardwareGroup->id,
                'email' => 'hardware-oracle@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 2,
            ],

            // Email Group
            [
                'name' => 'Email Case',
                'description' => 'Email Case Management',
                'department_group_id' => $emailGroup->id,
                'email' => 'emailcase@hospitalitytechnology.com.mv',
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }

        $this->command->info('✅ Departments seeded successfully!');
    }
}