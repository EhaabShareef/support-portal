<?php

namespace Database\Seeders;

use App\Models\ContractType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractTypes = [
            [
                'name' => 'Service Contract',
                'slug' => 'service_contract',
                'description' => 'Ongoing service and maintenance agreements',
                'sort_order' => 1,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Software License',
                'slug' => 'software_license',
                'description' => 'Software licensing agreements and subscriptions',
                'sort_order' => 2,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Hardware Warranty',
                'slug' => 'hardware_warranty',
                'description' => 'Hardware warranty and support agreements',
                'sort_order' => 3,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Consulting Agreement',
                'slug' => 'consulting_agreement',
                'description' => 'Professional services and consulting contracts',
                'sort_order' => 4,
                'is_protected' => false,
                'is_active' => true,
            ],
            [
                'name' => 'SLA Agreement',
                'slug' => 'sla_agreement',
                'description' => 'Service Level Agreement contracts',
                'sort_order' => 5,
                'is_protected' => true,
                'is_active' => true,
            ],
        ];

        foreach ($contractTypes as $type) {
            ContractType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
