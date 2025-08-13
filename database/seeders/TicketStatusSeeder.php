<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use App\Models\DepartmentGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ticketStatuses = [
            [
                'name' => 'Open',
                'key' => 'open',
                'description' => 'New ticket that has not been assigned or started',
                'color' => '#3b82f6',
                'sort_order' => 1,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'In Progress',
                'key' => 'in_progress',
                'description' => 'Ticket is currently being worked on',
                'color' => '#f59e0b',
                'sort_order' => 2,
                'is_protected' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Waiting for Customer',
                'key' => 'waiting_for_customer',
                'description' => 'Waiting for customer response or action',
                'color' => '#8b5cf6',
                'sort_order' => 3,
                'is_protected' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Resolved',
                'key' => 'resolved',
                'description' => 'Issue has been resolved, awaiting customer confirmation',
                'color' => '#10b981',
                'sort_order' => 4,
                'is_protected' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Closed',
                'key' => 'closed',
                'description' => 'Ticket has been completed and closed',
                'color' => '#6b7280',
                'sort_order' => 5,
                'is_protected' => true,
                'is_active' => true,
            ],
        ];

        foreach ($ticketStatuses as $statusData) {
            $status = TicketStatus::firstOrCreate(
                ['key' => $statusData['key']],
                $statusData
            );
        }

        // Assign all statuses to all department groups by default
        $departmentGroups = DepartmentGroup::all();
        $ticketStatuses = TicketStatus::all();

        foreach ($departmentGroups as $departmentGroup) {
            foreach ($ticketStatuses as $status) {
                $departmentGroup->ticketStatuses()->syncWithoutDetaching([$status->id]);
            }
        }
    }
}
