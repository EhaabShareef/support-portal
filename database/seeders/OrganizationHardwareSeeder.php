<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\OrganizationHardware;
use App\Models\OrganizationContract;
use App\Models\HardwareSerial;
use App\Models\HardwareType;
use Carbon\Carbon;

class OrganizationHardwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultOrg = Organization::where('name', 'Hospitality Technology')->first();
        
        if (!$defaultOrg) {
            $this->command->warn('Default organization "Hospitality Technology" not found. Skipping hardware seeding.');
            return;
        }

        // Get contracts for hardware assignment
        $contracts = OrganizationContract::where('organization_id', $defaultOrg->id)
            ->where('includes_hardware', true)
            ->get();
        
        if ($contracts->isEmpty()) {
            $this->command->warn('No hardware-enabled contracts found. Skipping hardware seeding.');
            return;
        }

        // Get hardware types
        $hardwareTypes = HardwareType::all()->keyBy('slug');
        
        $hardwareItems = [
            [
                'asset_tag' => 'HT-SRV-001',
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->where('is_oracle', true)->first()?->id ?? $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('server')?->id,
                'hardware_type' => 'Server',
                'brand' => 'Dell',
                'model' => 'PowerEdge R750',
                'quantity' => 1,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(8),
                'location' => 'Server Room A',
                'remarks' => 'Primary database server for Oracle PMS system.',
            ],
            [
                'asset_tag' => 'HT-SRV-002',
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('server')?->id,
                'hardware_type' => 'Server',
                'brand' => 'HP',
                'model' => 'ProLiant DL380 Gen10',
                'quantity' => 1,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(10),
                'location' => 'Server Room A',
                'remarks' => 'Secondary server for backup and redundancy.',
            ],
            [
                'asset_tag' => null,
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('desktop')?->id,
                'hardware_type' => 'Desktop',
                'brand' => 'Dell',
                'model' => 'OptiPlex 7090',
                'quantity' => 5,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(6),
                'location' => 'Front Desk',
                'remarks' => 'Desktop computers for front desk staff.',
            ],
            [
                'asset_tag' => null,
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('laptop')?->id,
                'hardware_type' => 'Laptop',
                'brand' => 'HP',
                'model' => 'EliteBook 840 G8',
                'quantity' => 3,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(4),
                'location' => 'Management Office',
                'remarks' => 'Laptops for management staff.',
            ],
            [
                'asset_tag' => 'HT-NET-001',
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('network_equipment')?->id,
                'hardware_type' => 'Network Equipment',
                'brand' => 'Cisco',
                'model' => 'Catalyst 9200-48T',
                'quantity' => 1,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(12),
                'location' => 'Network Closet',
                'remarks' => 'Core network switch for hotel operations.',
            ],
            [
                'asset_tag' => null,
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('printer')?->id,
                'hardware_type' => 'Printer',
                'brand' => 'HP',
                'model' => 'LaserJet Pro M404n',
                'quantity' => 4,
                'serial_required' => false,
                'purchase_date' => Carbon::now()->subMonths(5),
                'location' => 'Various Departments',
                'remarks' => 'Department printers for receipts and reports.',
            ],
            [
                'asset_tag' => null,
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('monitor')?->id,
                'hardware_type' => 'Monitor',
                'brand' => 'Dell',
                'model' => 'UltraSharp U2722DE',
                'quantity' => 8,
                'serial_required' => false,
                'purchase_date' => Carbon::now()->subMonths(6),
                'location' => 'Workstations',
                'remarks' => 'Monitors for desktop workstations.',
            ],
            [
                'asset_tag' => 'HT-STOR-001',
                'organization_id' => $defaultOrg->id,
                'contract_id' => $contracts->where('is_oracle', true)->first()?->id ?? $contracts->first()->id,
                'hardware_type_id' => $hardwareTypes->get('storage')?->id,
                'hardware_type' => 'Storage',
                'brand' => 'Synology',
                'model' => 'DiskStation DS1821+',
                'quantity' => 1,
                'serial_required' => true,
                'purchase_date' => Carbon::now()->subMonths(7),
                'location' => 'Server Room B',
                'remarks' => 'Network attached storage for backups and file sharing.',
            ],
        ];

        foreach ($hardwareItems as $hardwareData) {
            $hardware = OrganizationHardware::create($hardwareData);

            // Create sample serial numbers for hardware that requires them
            if ($hardware->serial_required && $hardware->quantity <= 2) {
                for ($i = 1; $i <= $hardware->quantity; $i++) {
                    $serialNumber = $this->generateSerial($hardware->brand, $hardware->model, $i);
                    
                    HardwareSerial::create([
                        'organization_hardware_id' => $hardware->id,
                        'serial' => $serialNumber,
                    ]);
                }
            }
        }

        $this->command->info('Created ' . count($hardwareItems) . ' sample hardware items for default organization.');
    }

    /**
     * Generate a realistic serial number
     */
    private function generateSerial(string $brand, string $model, int $index): string
    {
        $brandCode = strtoupper(substr($brand, 0, 2));
        $modelCode = strtoupper(substr(str_replace([' ', '-'], '', $model), 0, 4));
        $year = date('Y');
        $randomPart = str_pad($index, 3, '0', STR_PAD_LEFT) . rand(100, 999);
        
        return $brandCode . $modelCode . $year . $randomPart;
    }
}