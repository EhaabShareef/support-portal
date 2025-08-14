<?php

namespace Database\Seeders;

use App\Models\HardwareStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HardwareStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hardwareStatuses = [
            [
                'name' => 'Active',
                'slug' => 'active',
                'description' => 'Hardware is operational and in use',
                'sort_order' => 1,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Inactive',
                'slug' => 'inactive',
                'description' => 'Hardware is not currently in use but functional',
                'sort_order' => 2,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Under Maintenance',
                'slug' => 'under_maintenance',
                'description' => 'Hardware is undergoing scheduled maintenance',
                'sort_order' => 3,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Under Repair',
                'slug' => 'under_repair',
                'description' => 'Hardware is being repaired due to malfunction',
                'sort_order' => 4,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Retired',
                'slug' => 'retired',
                'description' => 'Hardware has been retired from service',
                'sort_order' => 5,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Lost/Stolen',
                'slug' => 'lost_stolen',
                'description' => 'Hardware has been reported as lost or stolen',
                'sort_order' => 6,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Disposed',
                'slug' => 'disposed',
                'description' => 'Hardware has been properly disposed of',
                'sort_order' => 7,
                'is_protected' => false,
                'is_active' => true,
            ],
            [
                'name' => 'In Transit',
                'slug' => 'in_transit',
                'description' => 'Hardware is being moved or shipped',
                'sort_order' => 8,
                'is_protected' => false,
                'is_active' => true,
            ],
        ];

        foreach ($hardwareStatuses as $status) {
            HardwareStatus::firstOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}
