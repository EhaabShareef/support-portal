<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DepartmentGroup;
use Illuminate\Support\Facades\DB;

class DepartmentGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding department groups...');

        // Clear existing department groups
        DepartmentGroup::query()->delete();

        $departmentGroups = [
            [
                'name' => 'Admin',
                'description' => 'Administrative group including Super Admin, Finance, HR, Project Management, and Sales',
                'color' => '#3b82f6', // blue
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'PMS',
                'description' => 'Property Management System departments',
                'color' => '#10b981', // green
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'POS',
                'description' => 'Point of Sale System departments',
                'color' => '#f59e0b', // amber
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'MC',
                'description' => 'Materials Control department',
                'color' => '#8b5cf6', // violet
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'BO',
                'description' => 'BackOffice department',
                'color' => '#ec4899', // pink
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Hardware',
                'description' => 'Hardware support departments',
                'color' => '#ef4444', // red
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Email',
                'description' => 'Email Case management department',
                'color' => '#06b6d4', // cyan
                'is_active' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($departmentGroups as $group) {
            DepartmentGroup::create($group);
        }

        $this->command->info('✅ Department groups seeded successfully!');
    }
}