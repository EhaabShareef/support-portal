<?php

namespace Database\Seeders;

use App\Models\HardwareType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HardwareTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hardwareTypes = [
            [
                'name' => 'Desktop Computer',
                'slug' => 'desktop_computer',
                'description' => 'Desktop computers and workstations',
                'sort_order' => 1,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Laptop Computer',
                'slug' => 'laptop_computer',
                'description' => 'Laptops and mobile workstations',
                'sort_order' => 2,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Server',
                'slug' => 'server',
                'description' => 'Physical and virtual servers',
                'sort_order' => 3,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Network Equipment',
                'slug' => 'network_equipment',
                'description' => 'Routers, switches, and network infrastructure',
                'sort_order' => 4,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Printer',
                'slug' => 'printer',
                'description' => 'Printers and printing devices',
                'sort_order' => 5,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Monitor',
                'slug' => 'monitor',
                'description' => 'Computer monitors and displays',
                'sort_order' => 6,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Mobile Device',
                'slug' => 'mobile_device',
                'description' => 'Smartphones and tablets',
                'sort_order' => 7,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Storage Device',
                'slug' => 'storage_device',
                'description' => 'External storage and backup devices',
                'sort_order' => 8,
                'is_protected' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Peripheral',
                'slug' => 'peripheral',
                'description' => 'Keyboards, mice, and other accessories',
                'sort_order' => 9,
                'is_protected' => false,
                'is_active' => true,
            ],
        ];

        foreach ($hardwareTypes as $type) {
            HardwareType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
