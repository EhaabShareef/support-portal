<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SampleTicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultOrg = Organization::where('name', 'Hospitality Technology')->first();
        
        if (!$defaultOrg) {
            $this->command->warn('Default organization "Hospitality Technology" not found. Skipping ticket seeding.');
            return;
        }

        // Get sample users
        $clients = User::whereHas('roles', function($q) { $q->where('name', 'Client'); })->take(3)->get();
        $supportUsers = User::whereHas('roles', function($q) { $q->where('name', 'Support'); })->take(3)->get();
        $departments = Department::all();
        
        if ($clients->isEmpty() || $supportUsers->isEmpty() || $departments->isEmpty()) {
            $this->command->warn('Required users or departments not found. Skipping ticket seeding.');
            return;
        }

        $ticketTemplates = [
            [
                'subject' => 'PMS System Login Issues',
                'description' => 'Users are experiencing difficulty logging into the PMS system this morning. Multiple front desk staff affected.',
                'priority' => 'high',
                'status' => 'open',
                'department' => 'PMS',
            ],
            [
                'subject' => 'POS Terminal Not Responding',
                'description' => 'POS terminal #3 in the restaurant has stopped responding to touch input. Affecting guest checkout process.',
                'priority' => 'urgent',
                'status' => 'in_progress',
                'department' => 'POS',
            ],
            [
                'subject' => 'WiFi Connectivity Problems in Conference Room',
                'description' => 'Guests are reporting poor WiFi connectivity in Conference Room B. Signal strength appears weak.',
                'priority' => 'normal',
                'status' => 'waiting_for_customer',
                'department' => 'MC',
            ],
            [
                'subject' => 'Database Backup Failure',
                'description' => 'Automated database backup failed last night. Need to investigate and ensure data integrity.',
                'priority' => 'high',
                'status' => 'in_progress',
                'department' => 'PMS',
            ],
            [
                'subject' => 'New Staff Training Request',
                'description' => 'Need to schedule training session for 3 new front desk staff members on PMS operations.',
                'priority' => 'normal',
                'status' => 'open',
                'department' => 'Admin',
            ],
            [
                'subject' => 'Printer Replacement Required',
                'description' => 'Receipt printer at front desk is constantly jamming and needs replacement.',
                'priority' => 'normal',
                'status' => 'resolved',
                'department' => 'Hardware',
            ],
            [
                'subject' => 'Server Performance Issues',
                'description' => 'Database server is running slowly during peak hours. Response times are affecting guest experience.',
                'priority' => 'critical',
                'status' => 'in_progress',
                'department' => 'PMS',
            ],
            [
                'subject' => 'Software License Renewal',
                'description' => 'Oracle license for PMS system expires next month. Need to process renewal.',
                'priority' => 'normal',
                'status' => 'open',
                'department' => 'Admin',
            ],
            [
                'subject' => 'Network Switch Maintenance',
                'description' => 'Scheduled maintenance completed on core network switch. All systems back online.',
                'priority' => 'low',
                'status' => 'closed',
                'department' => 'MC',
            ],
            [
                'subject' => 'Guest Complaint - Room Key Issues',
                'description' => 'Guest in Room 305 reports room key not working. Key card system needs investigation.',
                'priority' => 'normal',
                'status' => 'resolved',
                'department' => 'PMS',
            ],
        ];

        $ticketCounter = 1;
        
        foreach ($ticketTemplates as $template) {
            $department = $departments->where('name', $template['department'])->first() 
                         ?? $departments->first();
            
            $client = $clients->random();
            $owner = $supportUsers->random();
            
            // Create base ticket data
            $ticketData = [
                'uuid' => Str::uuid(),
                'ticket_number' => 'HT-' . str_pad($ticketCounter, 6, '0', STR_PAD_LEFT),
                'subject' => $template['subject'],
                'status' => $template['status'],
                'priority' => $template['priority'],
                'description' => $template['description'], // Will be migrated to messages
                'organization_id' => $defaultOrg->id,
                'client_id' => $client->id,
                'department_id' => $department->id,
                'owner_id' => in_array($template['status'], ['in_progress', 'waiting_for_customer', 'resolved']) ? $owner->id : null,
                'created_at' => Carbon::now()->subDays(rand(1, 30)),
                'updated_at' => Carbon::now()->subDays(rand(0, 5)),
            ];

            // Set timestamps based on status
            if (in_array($template['status'], ['resolved', 'closed'])) {
                $ticketData['resolved_at'] = Carbon::now()->subDays(rand(0, 3));
                $ticketData['first_response_at'] = Carbon::parse($ticketData['created_at'])->addHours(rand(1, 8));
            }
            
            if ($template['status'] === 'closed') {
                $ticketData['closed_at'] = Carbon::now()->subDays(rand(0, 2));
            }

            $ticket = Ticket::create($ticketData);

            // Create initial message from description
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $client->id,
                'message' => $template['description'],
                'is_internal' => false,
                'created_at' => $ticket->created_at,
                'updated_at' => $ticket->created_at,
            ]);

            // Add follow-up messages for active tickets
            if (in_array($template['status'], ['in_progress', 'waiting_for_customer', 'resolved'])) {
                $followUpMessages = $this->getFollowUpMessages($template['status']);
                
                foreach ($followUpMessages as $index => $messageData) {
                    TicketMessage::create([
                        'ticket_id' => $ticket->id,
                        'sender_id' => $messageData['from_client'] ? $client->id : $owner->id,
                        'message' => $messageData['message'],
                        'is_internal' => $messageData['internal'] ?? false,
                        'created_at' => Carbon::parse($ticket->created_at)->addHours(($index + 1) * 4),
                        'updated_at' => Carbon::parse($ticket->created_at)->addHours(($index + 1) * 4),
                    ]);
                }
            }

            $ticketCounter++;
        }

        $this->command->info('Created ' . count($ticketTemplates) . ' sample tickets with messages for default organization.');
    }

    /**
     * Get follow-up messages based on ticket status
     */
    private function getFollowUpMessages(string $status): array
    {
        $messages = [
            'in_progress' => [
                ['message' => 'Thank you for reporting this issue. We are currently investigating and will provide an update shortly.', 'from_client' => false],
                ['message' => 'I have assigned a technician to look into this matter. We should have a resolution within the next few hours.', 'from_client' => false],
            ],
            'waiting_for_customer' => [
                ['message' => 'We have identified a potential solution. Could you please confirm if the issue persists after clearing your browser cache?', 'from_client' => false],
                ['message' => 'Please let us know if you need any additional assistance with testing the proposed solution.', 'from_client' => false],
            ],
            'resolved' => [
                ['message' => 'We have implemented a fix for this issue. Please test and confirm if everything is working correctly now.', 'from_client' => false],
                ['message' => 'The issue has been resolved. Thank you for your patience.', 'from_client' => true],
            ],
        ];

        return $messages[$status] ?? [];
    }
}