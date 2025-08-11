<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Models\Ticket;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ClientSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds to create sample client data.
     */
    public function run(): void
    {
        $this->command->info('🏨 Seeding sample client data...');

        // Create client organizations (resorts/hotels)
        $organizations = $this->createOrganizations();
        
        // Create users for each organization
        $this->createUsersForOrganizations($organizations);
        
        // Create contracts for organizations
        $this->createOrganizationContracts($organizations);
        
        // Create hardware for organizations
        $this->createOrganizationHardware($organizations);
        
        // Create sample tickets
        $this->createSampleTickets($organizations);

        $this->command->info('✅ Sample client data seeded successfully!');
        $this->command->info('📊 Created 3 organizations with users, contracts, hardware, and tickets');
    }

    private function createOrganizations(): array
    {
        $this->command->info('Creating sample organizations...');
        
        $organizationsData = [
            [
                'name' => 'Paradise Bay Resort & Spa',
                'company' => 'Paradise Bay Hospitality Ltd.',
                'company_contact' => 'Sarah Mitchell',
                'tin_no' => 'PBR2023001',
                'email' => 'info@paradisebayresort.com',
                'phone' => '+960-664-1234',
                'is_active' => true,
                'subscription_status' => 'active',
                'notes' => '5-star luxury resort with 120 rooms, multiple restaurants and spa facilities'
            ],
            [
                'name' => 'Crystal Waters Hotel',
                'company' => 'Crystal Waters International',
                'company_contact' => 'Ahmed Hassan',
                'tin_no' => 'CWH2023002',
                'email' => 'contact@crystalwatershotel.com',
                'phone' => '+960-665-5678',
                'is_active' => true,
                'subscription_status' => 'active',
                'notes' => '4-star business hotel with 80 rooms and conference facilities'
            ],
            [
                'name' => 'Sunset Villas Resort',
                'company' => 'Sunset Hospitality Group',
                'company_contact' => 'Maria Rodriguez',
                'tin_no' => 'SVR2023003',
                'email' => 'reservations@sunsetvillasresort.com',
                'phone' => '+960-666-9012',
                'is_active' => true,
                'subscription_status' => 'active',
                'notes' => 'Boutique villa resort with 45 villas and overwater bungalows'
            ]
        ];

        $organizations = [];
        foreach ($organizationsData as $orgData) {
            $org = Organization::updateOrCreate(
                ['tin_no' => $orgData['tin_no']],
                $orgData
            );
            $organizations[] = $org;
            $this->command->info("✓ Created organization: {$org->name}");
        }

        return $organizations;
    }

    private function createUsersForOrganizations(array $organizations): void
    {
        $this->command->info('Creating users for organizations...');
        
        $clientRole = Role::where('name', 'client')->first();
        if (!$clientRole) {
            $this->command->warn('Client role not found. Skipping user creation.');
            return;
        }

        $usersData = [
            // Paradise Bay Resort & Spa users
            [
                'org_index' => 0,
                'users' => [
                    ['name' => 'Sarah Mitchell', 'username' => 'sarah.mitchell', 'email' => 'sarah.mitchell@paradisebayresort.com', 'title' => 'General Manager'],
                    ['name' => 'James Wilson', 'username' => 'james.wilson', 'email' => 'james.wilson@paradisebayresort.com', 'title' => 'IT Manager'],
                    ['name' => 'Emma Thompson', 'username' => 'emma.thompson', 'email' => 'emma.thompson@paradisebayresort.com', 'title' => 'Front Office Manager'],
                    ['name' => 'David Chen', 'username' => 'david.chen', 'email' => 'david.chen@paradisebayresort.com', 'title' => 'F&B Manager']
                ]
            ],
            // Crystal Waters Hotel users
            [
                'org_index' => 1,
                'users' => [
                    ['name' => 'Ahmed Hassan', 'username' => 'ahmed.hassan', 'email' => 'ahmed.hassan@crystalwatershotel.com', 'title' => 'Hotel Manager'],
                    ['name' => 'Lisa Anderson', 'username' => 'lisa.anderson', 'email' => 'lisa.anderson@crystalwatershotel.com', 'title' => 'Operations Manager'],
                    ['name' => 'Michael Brown', 'username' => 'michael.brown', 'email' => 'michael.brown@crystalwatershotel.com', 'title' => 'Systems Administrator']
                ]
            ],
            // Sunset Villas Resort users
            [
                'org_index' => 2,
                'users' => [
                    ['name' => 'Maria Rodriguez', 'username' => 'maria.rodriguez', 'email' => 'maria.rodriguez@sunsetvillasresort.com', 'title' => 'Resort Manager'],
                    ['name' => 'Alex Turner', 'username' => 'alex.turner', 'email' => 'alex.turner@sunsetvillasresort.com', 'title' => 'Guest Relations Manager'],
                    ['name' => 'Sophie Laurent', 'username' => 'sophie.laurent', 'email' => 'sophie.laurent@sunsetvillasresort.com', 'title' => 'Revenue Manager'],
                    ['name' => 'Carlos Mendez', 'username' => 'carlos.mendez', 'email' => 'carlos.mendez@sunsetvillasresort.com', 'title' => 'Maintenance Supervisor'],
                    ['name' => 'Jenny Kim', 'username' => 'jenny.kim', 'email' => 'jenny.kim@sunsetvillasresort.com', 'title' => 'Spa Manager']
                ]
            ]
        ];

        foreach ($usersData as $orgUsers) {
            $organization = $organizations[$orgUsers['org_index']];
            
            foreach ($orgUsers['users'] as $userData) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'uuid' => Str::uuid(),
                        'name' => $userData['name'],
                        'username' => $userData['username'],
                        'password' => Hash::make('client123'),
                        'organization_id' => $organization->id,
                        'department_id' => null, // Client users not assigned to internal departments
                        'email_verified_at' => now(),
                        'is_active' => true,
                        'preferences' => json_encode(['title' => $userData['title']])
                    ]
                );
                
                $user->assignRole($clientRole);
                $this->command->info("✓ Created user: {$user->name} ({$organization->name})");
            }
        }
    }

    private function createOrganizationContracts(array $organizations): void
    {
        $this->command->info('Creating organization contracts...');
        
        $departments = Department::all()->keyBy('name');
        
        $contractsData = [
            // Paradise Bay Resort & Spa contracts
            [
                'org_index' => 0,
                'contracts' => [
                    [
                        'department_name' => 'Opera',
                        'service_type' => 'PMS Support',
                        'start_date' => '2023-01-01',
                        'end_date' => '2024-12-31',
                        'value' => 45000.00,
                        'status' => 'active',
                        'description' => 'Annual OPERA PMS support and maintenance contract'
                    ],
                    [
                        'department_name' => 'Simphny',
                        'service_type' => 'POS Support',
                        'start_date' => '2023-03-01',
                        'end_date' => '2024-02-29',
                        'value' => 28000.00,
                        'status' => 'active',
                        'description' => 'Symphony POS system support for all F&B outlets'
                    ],
                    [
                        'department_name' => 'Technical',
                        'service_type' => 'Hardware Maintenance',
                        'start_date' => '2023-01-01',
                        'end_date' => '2025-12-31',
                        'value' => 35000.00,
                        'status' => 'active',
                        'description' => 'Comprehensive hardware maintenance and replacement'
                    ]
                ]
            ],
            // Crystal Waters Hotel contracts
            [
                'org_index' => 1,
                'contracts' => [
                    [
                        'department_name' => 'Opera Cloud',
                        'service_type' => 'Cloud PMS',
                        'start_date' => '2023-06-01',
                        'end_date' => '2024-05-31',
                        'value' => 32000.00,
                        'status' => 'active',
                        'description' => 'OPERA Cloud PMS subscription and support'
                    ],
                    [
                        'department_name' => 'RES 9700',
                        'service_type' => 'POS Support',
                        'start_date' => '2023-04-01',
                        'end_date' => '2024-03-31',
                        'value' => 18000.00,
                        'status' => 'active',
                        'description' => 'RES 9700 POS system support and updates'
                    ]
                ]
            ],
            // Sunset Villas Resort contracts
            [
                'org_index' => 2,
                'contracts' => [
                    [
                        'department_name' => 'Opera',
                        'service_type' => 'PMS Support',
                        'start_date' => '2023-02-01',
                        'end_date' => '2024-01-31',
                        'value' => 25000.00,
                        'status' => 'active',
                        'description' => 'OPERA PMS support for boutique operations'
                    ],
                    [
                        'department_name' => 'Materials Control',
                        'service_type' => 'Materials Control',
                        'start_date' => '2023-05-01',
                        'end_date' => '2024-04-30',
                        'value' => 15000.00,
                        'status' => 'active',
                        'description' => 'Materials Control system implementation and support'
                    ]
                ]
            ]
        ];

        foreach ($contractsData as $orgContracts) {
            $organization = $organizations[$orgContracts['org_index']];
            
            foreach ($orgContracts['contracts'] as $contractData) {
                $department = $departments->get($contractData['department_name']);
                if (!$department) continue;

                // Map service types to enum values
                $typeMapping = [
                    'PMS Support' => 'support',
                    'POS Support' => 'support', 
                    'Cloud PMS' => 'software',
                    'Hardware Maintenance' => 'hardware',
                    'Materials Control' => 'software'
                ];
                $contractType = $typeMapping[$contractData['service_type']] ?? 'support';
                
                $contract = OrganizationContract::updateOrCreate([
                    'organization_id' => $organization->id,
                    'department_id' => $department->id,
                    'type' => $contractType
                ], [
                    'contract_number' => 'CNT-' . strtoupper(substr($organization->name, 0, 3)) . '-' . date('Y') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'start_date' => $contractData['start_date'],
                    'end_date' => $contractData['end_date'],
                    'contract_value' => $contractData['value'],
                    'status' => $contractData['status'],
                    'terms_conditions' => $contractData['description']
                ]);
                
                $this->command->info("✓ Created contract: {$contractData['service_type']} for {$organization->name}");
            }
        }
    }

    private function createOrganizationHardware(array $organizations): void
    {
        $this->command->info('Creating organization hardware...');
        
        $hardwareData = [
            // Paradise Bay Resort & Spa hardware
            [
                'org_index' => 0,
                'hardware' => [
                    ['type' => 'Server', 'model' => 'Dell PowerEdge R740', 'serial' => 'PBR-SRV-001', 'location' => 'Main Server Room', 'status' => 'active'],
                    ['type' => 'Workstation', 'model' => 'HP EliteDesk 800 G6', 'serial' => 'PBR-WS-FO1', 'location' => 'Front Office', 'status' => 'active'],
                    ['type' => 'Workstation', 'model' => 'HP EliteDesk 800 G6', 'serial' => 'PBR-WS-FO2', 'location' => 'Front Office', 'status' => 'active'],
                    ['type' => 'POS Terminal', 'model' => 'Micros WS5A', 'serial' => 'PBR-POS-R01', 'location' => 'Main Restaurant', 'status' => 'active'],
                    ['type' => 'POS Terminal', 'model' => 'Micros WS5A', 'serial' => 'PBR-POS-B01', 'location' => 'Pool Bar', 'status' => 'active'],
                    ['type' => 'Printer', 'model' => 'HP LaserJet Pro 404dn', 'serial' => 'PBR-PRT-001', 'location' => 'Reception', 'status' => 'active']
                ]
            ],
            // Crystal Waters Hotel hardware
            [
                'org_index' => 1,
                'hardware' => [
                    ['type' => 'Server', 'model' => 'HPE ProLiant DL380 Gen10', 'serial' => 'CWH-SRV-001', 'location' => 'IT Room', 'status' => 'active'],
                    ['type' => 'Workstation', 'model' => 'Lenovo ThinkCentre M720q', 'serial' => 'CWH-WS-001', 'location' => 'Front Desk', 'status' => 'active'],
                    ['type' => 'POS Terminal', 'model' => 'NCR RealPOS 82XRT', 'serial' => 'CWH-POS-001', 'location' => 'Restaurant', 'status' => 'active'],
                    ['type' => 'Network Switch', 'model' => 'Cisco Catalyst 2960-X', 'serial' => 'CWH-SW-001', 'location' => 'Network Cabinet', 'status' => 'active']
                ]
            ],
            // Sunset Villas Resort hardware
            [
                'org_index' => 2,
                'hardware' => [
                    ['type' => 'Server', 'model' => 'Dell PowerEdge R540', 'serial' => 'SVR-SRV-001', 'location' => 'Admin Building', 'status' => 'active'],
                    ['type' => 'Workstation', 'model' => 'ASUS ExpertCenter D300TA', 'serial' => 'SVR-WS-001', 'location' => 'Reception', 'status' => 'active'],
                    ['type' => 'Tablet', 'model' => 'iPad Pro 12.9"', 'serial' => 'SVR-TAB-001', 'location' => 'Guest Services', 'status' => 'active'],
                    ['type' => 'POS Terminal', 'model' => 'Square Terminal', 'serial' => 'SVR-POS-001', 'location' => 'Spa Reception', 'status' => 'active'],
                    ['type' => 'Wireless Access Point', 'model' => 'Ubiquiti UniFi AP AC Pro', 'serial' => 'SVR-AP-001', 'location' => 'Villa Area', 'status' => 'maintenance']
                ]
            ]
        ];

        foreach ($hardwareData as $orgHardware) {
            $organization = $organizations[$orgHardware['org_index']];
            
            foreach ($orgHardware['hardware'] as $hardware) {
                OrganizationHardware::updateOrCreate([
                    'organization_id' => $organization->id,
                    'serial_number' => $hardware['serial']
                ], [
                    'asset_tag' => 'AST-' . $hardware['serial'],
                    'hardware_type' => $hardware['type'],
                    'brand' => explode(' ', $hardware['model'])[0] ?? 'Generic',
                    'model' => $hardware['model'],
                    'location' => $hardware['location'],
                    'status' => $hardware['status'],
                    'purchase_date' => Carbon::now()->subMonths(rand(3, 18)),
                    'warranty_start' => Carbon::now()->subMonths(rand(3, 18)),
                    'warranty_expiration' => Carbon::now()->addMonths(rand(12, 36))
                ]);
                
                $this->command->info("✓ Created hardware: {$hardware['type']} for {$organization->name}");
            }
        }
    }

    private function createSampleTickets(array $organizations): void
    {
        $this->command->info('Creating sample tickets...');
        
        $departments = Department::all()->keyBy('name');
        $supportUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'support']);
        })->get();

        $ticketsData = [
            // Paradise Bay Resort tickets
            [
                'org_index' => 0,
                'tickets' => [
                    [
                        'subject' => 'OPERA System Running Slow During Check-in Rush',
                        'description' => 'The OPERA system becomes extremely slow during peak check-in times (3-6 PM). Guest wait times are increasing and front desk staff are frustrated.',
                        'priority' => 'high',
                        'department' => 'Opera'
                    ],
                    [
                        'subject' => 'Pool Bar POS Terminal Not Printing Receipts',
                        'description' => 'The Symphony POS terminal at the pool bar stopped printing receipts yesterday. Guests are asking for receipts but we cannot provide them.',
                        'priority' => 'urgent',
                        'department' => 'Simphny'
                    ],
                    [
                        'subject' => 'Request for Additional User Training on OPERA Reports',
                        'description' => 'Our new front office manager needs training on generating daily and monthly reports in OPERA. Please schedule a training session.',
                        'priority' => 'normal',
                        'department' => 'Opera'
                    ]
                ]
            ],
            // Crystal Waters Hotel tickets
            [
                'org_index' => 1,
                'tickets' => [
                    [
                        'subject' => 'OPERA Cloud Connection Issues',
                        'description' => 'We are experiencing intermittent connection issues with OPERA Cloud. The system disconnects randomly during the day causing disruptions.',
                        'priority' => 'high',
                        'department' => 'Opera Cloud'
                    ],
                    [
                        'subject' => 'Hardware Upgrade Request for Front Desk Workstation',
                        'description' => 'The current front desk workstation is 5 years old and running very slowly. We need to upgrade to improve efficiency.',
                        'priority' => 'normal',
                        'department' => 'Technical'
                    ],
                    [
                        'subject' => 'RES 9700 Menu Update Not Working',
                        'description' => 'We updated our restaurant menu in the system but the changes are not reflecting on the POS terminals. The old menu items are still showing.',
                        'priority' => 'high',
                        'department' => 'RES 9700'
                    ]
                ]
            ],
            // Sunset Villas Resort tickets
            [
                'org_index' => 2,
                'tickets' => [
                    [
                        'subject' => 'WiFi Coverage Issues in Villa 12-15',
                        'description' => 'Guests in villas 12-15 are complaining about poor WiFi connectivity. The signal is weak and keeps dropping.',
                        'priority' => 'high',
                        'department' => 'Technical'
                    ],
                    [
                        'subject' => 'Materials Control System Training Request',
                        'description' => 'Our housekeeping supervisor needs training on the new Materials Control system for inventory management.',
                        'priority' => 'normal',
                        'department' => 'Materials Control'
                    ],
                    [
                        'subject' => 'OPERA Backup Issues',
                        'description' => 'The nightly backup for OPERA has been failing for the past 3 days. We need this resolved immediately to ensure data safety.',
                        'priority' => 'critical',
                        'department' => 'Opera'
                    ],
                    [
                        'subject' => 'Spa POS Terminal Needs Replacement',
                        'description' => 'The Square terminal at the spa reception is malfunctioning. The screen flickers and sometimes freezes during transactions.',
                        'priority' => 'high',
                        'department' => 'Technical'
                    ]
                ]
            ]
        ];

        $ticketNumber = 1001;
        foreach ($ticketsData as $orgTickets) {
            $organization = $organizations[$orgTickets['org_index']];
            $orgUsers = User::where('organization_id', $organization->id)->get();
            
            foreach ($orgTickets['tickets'] as $ticketData) {
                $department = $departments->get($ticketData['department']);
                if (!$department || $orgUsers->isEmpty()) continue;

                $clientUser = $orgUsers->random();
                $assignedUser = $supportUsers->random();

                $createdAt = Carbon::now()->subDays(rand(1, 30));
                $ticket = new Ticket([
                    'uuid' => Str::uuid(),
                    'ticket_number' => 'TKT-' . str_pad($ticketNumber++, 6, '0', STR_PAD_LEFT),
                    'subject' => $ticketData['subject'],
                    'status' => collect(['open', 'in_progress', 'awaiting_customer_response'])->random(),
                    'priority' => $ticketData['priority'],
                    'description' => $ticketData['description'],
                    'organization_id' => $organization->id,
                    'client_id' => $clientUser->id,
                    'department_id' => $department->id,
                    'assigned_to' => rand(0, 1) ? $assignedUser->id : null,
                ]);
                $ticket->created_at = $createdAt;
                $ticket->updated_at = $createdAt;
                $ticket->save();
                
                $this->command->info("✓ Created ticket: {$ticket->ticket_number} for {$organization->name}");
            }
        }
    }
}