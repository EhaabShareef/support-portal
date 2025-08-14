<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\OrganizationContract;
use App\Models\Department;
use Carbon\Carbon;

class OrganizationContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultOrg = Organization::where('name', 'Hospitality Technology')->first();
        
        if (!$defaultOrg) {
            $this->command->warn('Default organization "Hospitality Technology" not found. Skipping contract seeding.');
            return;
        }

        $departments = Department::all()->pluck('id')->toArray();
        
        if (empty($departments)) {
            $this->command->warn('No departments found. Skipping contract seeding.');
            return;
        }

        $contracts = [
            [
                'contract_number' => 'HT-SUP-2024-001',
                'organization_id' => $defaultOrg->id,
                'department_id' => $departments[array_rand($departments)],
                'type' => 'support',
                'status' => 'active',
                'includes_hardware' => true,
                'is_oracle' => false,
                'start_date' => Carbon::now()->subMonths(6),
                'end_date' => Carbon::now()->addMonths(18),
                'renewal_months' => 12,
                'notes' => 'Main support contract covering PMS and POS systems with hardware maintenance included.',
            ],
            [
                'contract_number' => 'HT-ORC-2024-002',
                'organization_id' => $defaultOrg->id,
                'department_id' => $departments[array_rand($departments)],
                'type' => 'software',
                'status' => 'active',
                'includes_hardware' => true,
                'is_oracle' => true,
                'csi_number' => 'CSI-789012345',
                'start_date' => Carbon::now()->subMonths(12),
                'end_date' => Carbon::now()->addMonths(12),
                'renewal_months' => 24,
                'notes' => 'Oracle software license with hardware support coverage for database servers.',
            ],
            [
                'contract_number' => 'HT-HW-2024-003',
                'organization_id' => $defaultOrg->id,
                'department_id' => $departments[array_rand($departments)],
                'type' => 'hardware',
                'status' => 'active',
                'includes_hardware' => true,
                'is_oracle' => false,
                'start_date' => Carbon::now()->subMonths(3),
                'end_date' => Carbon::now()->addMonths(21),
                'renewal_months' => 12,
                'notes' => 'Hardware warranty and maintenance contract for network equipment and servers.',
            ],
            [
                'contract_number' => 'HT-CON-2024-004',
                'organization_id' => $defaultOrg->id,
                'department_id' => $departments[array_rand($departments)],
                'type' => 'consulting',
                'status' => 'active',
                'includes_hardware' => false,
                'is_oracle' => false,
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'renewal_months' => 6,
                'notes' => 'Consulting services for system optimization and staff training.',
            ],
            [
                'contract_number' => 'HT-MAINT-2024-005',
                'organization_id' => $defaultOrg->id,
                'department_id' => $departments[array_rand($departments)],
                'type' => 'maintenance',
                'status' => 'active',
                'includes_hardware' => true,
                'is_oracle' => false,
                'start_date' => Carbon::now()->subYears(2),
                'end_date' => Carbon::now()->addMonths(2),
                'renewal_months' => 12,
                'notes' => 'Legacy maintenance contract expiring soon. Renewal discussions in progress.',
            ],
        ];

        foreach ($contracts as $contractData) {
            OrganizationContract::create($contractData);
        }

        $this->command->info('Created ' . count($contracts) . ' sample contracts for default organization.');
    }
}