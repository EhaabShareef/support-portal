<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrganizationSubscriptionStatus;

class OrganizationSubscriptionStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'key' => 'trial',
                'label' => 'Trial',
                'color' => '#f59e0b', // amber
                'sort_order' => 1,
                'is_active' => true,
                'description' => 'Organization is in trial period'
            ],
            [
                'key' => 'active',
                'label' => 'Active',
                'color' => '#10b981', // green
                'sort_order' => 2,
                'is_active' => true,
                'description' => 'Organization has active subscription'
            ],
            [
                'key' => 'suspended',
                'label' => 'Suspended',
                'color' => '#f97316', // orange
                'sort_order' => 3,
                'is_active' => true,
                'description' => 'Organization subscription is temporarily suspended'
            ],
            [
                'key' => 'cancelled',
                'label' => 'Cancelled',
                'color' => '#ef4444', // red
                'sort_order' => 4,
                'is_active' => true,
                'description' => 'Organization subscription has been cancelled'
            ],
            [
                'key' => 'pending',
                'label' => 'Pending',
                'color' => '#6b7280', // gray
                'sort_order' => 5,
                'is_active' => true,
                'description' => 'Organization subscription is pending activation'
            ],
        ];

        foreach ($statuses as $status) {
            OrganizationSubscriptionStatus::firstOrCreate(
                ['key' => $status['key']],
                $status
            );
        }
    }
}