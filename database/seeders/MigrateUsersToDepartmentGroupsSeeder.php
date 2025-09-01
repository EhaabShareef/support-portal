<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateUsersToDepartmentGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // If legacy column exists, migrate from department_id -> department_group_id
        if (Schema::hasColumn('users', 'department_id')) {
            $usersWithDepartments = User::whereNotNull('department_id')->get();

            foreach ($usersWithDepartments as $user) {
                $department = Department::find($user->department_id);

                if ($department && $department->department_group_id) {
                    $user->update(['department_group_id' => $department->department_group_id]);
                } else {
                    $defaultGroup = DepartmentGroup::firstOrCreate(
                        ['name' => 'Default Group'],
                        [
                            'description' => 'Default department group for users without assigned groups',
                            'color' => '#6b7280',
                            'is_active' => true,
                            'sort_order' => 999,
                        ]
                    );

                    $user->update(['department_group_id' => $defaultGroup->id]);

                    if ($department) {
                        $department->update(['department_group_id' => $defaultGroup->id]);
                    }
                }
            }

            // Assign remaining users without departments to Unassigned
            $usersWithoutDepartments = User::whereNull('department_id')
                ->whereNull('department_group_id')
                ->get();

            if ($usersWithoutDepartments->count() > 0) {
                $unassignedGroup = DepartmentGroup::firstOrCreate(
                    ['name' => 'Unassigned'],
                    [
                        'description' => 'Users without specific department group assignments',
                        'color' => '#9ca3af',
                        'is_active' => true,
                        'sort_order' => 1000,
                    ]
                );

                User::whereNull('department_id')
                    ->whereNull('department_group_id')
                    ->update(['department_group_id' => $unassignedGroup->id]);
            }
            return; // done with legacy path
        }

        // Fresh schema path: only ensure all users have a department_group_id
        $usersNeedingGroup = User::whereNull('department_group_id')->get();
        if ($usersNeedingGroup->isNotEmpty()) {
            $unassignedGroup = DepartmentGroup::firstOrCreate(
                ['name' => 'Unassigned'],
                [
                    'description' => 'Users without specific department group assignments',
                    'color' => '#9ca3af',
                    'is_active' => true,
                    'sort_order' => 1000,
                ]
            );

            User::whereNull('department_group_id')
                ->update(['department_group_id' => $unassignedGroup->id]);
        }
    }
}
