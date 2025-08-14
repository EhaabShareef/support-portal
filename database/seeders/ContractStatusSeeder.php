<?php

namespace Database\Seeders;

use App\Models\ContractStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContractStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractStatuses = [
            [
                'name' => 'Draft',
                'slug' => 'draft',
                'description' => 'Contract is being prepared and not yet finalized',
                'color' => '#6b7280',
                'sort_order' => 1,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Pending Review',
                'slug' => 'pending_review',
                'description' => 'Contract is awaiting review and approval',
                'color' => '#f59e0b',
                'sort_order' => 2,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Active',
                'slug' => 'active',
                'description' => 'Contract is currently in effect and active',
                'color' => '#10b981',
                'sort_order' => 3,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Expiring Soon',
                'slug' => 'expiring_soon',
                'description' => 'Contract is approaching expiration date',
                'color' => '#f97316',
                'sort_order' => 4,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Expired',
                'slug' => 'expired',
                'description' => 'Contract has passed its expiration date',
                'color' => '#ef4444',
                'sort_order' => 5,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Terminated',
                'slug' => 'terminated',
                'description' => 'Contract has been terminated before expiration',
                'color' => '#dc2626',
                'sort_order' => 6,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Renewed',
                'slug' => 'renewed',
                'description' => 'Contract has been renewed with updated terms',
                'color' => '#3b82f6',
                'sort_order' => 7,
                'is_protected' => false,
                'is_active' => true,
            ],
        ];

        foreach ($contractStatuses as $status) {
            ContractStatus::firstOrCreate(
                ['slug' => $status['slug']],
                $status
            );
        }
    }
}
