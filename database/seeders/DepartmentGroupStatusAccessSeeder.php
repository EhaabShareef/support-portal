<?php

namespace Database\Seeders;

use App\Models\DepartmentGroup;
use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class DepartmentGroupStatusAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Seeding department group status access...');

        // Get department groups
        $pmsGroup = DepartmentGroup::where('name', 'PMS')->first();
        $hardwareGroup = DepartmentGroup::where('name', 'Hardware')->first();
        $posGroup = DepartmentGroup::where('name', 'POS')->first();
        $mcGroup = DepartmentGroup::where('name', 'MC')->first();
        $adminGroup = DepartmentGroup::where('name', 'Admin')->first();

        // Get ticket statuses
        $openStatus = TicketStatus::where('key', 'open')->first();
        $inProgressStatus = TicketStatus::where('key', 'in_progress')->first();
        $waitingStatus = TicketStatus::where('key', 'waiting_for_customer')->first();
        $resolvedStatus = TicketStatus::where('key', 'resolved')->first();
        $closedStatus = TicketStatus::where('key', 'closed')->first();

        // Clear existing relationships
        TicketStatus::all()->each(function ($status) {
            $status->departmentGroups()->detach();
        });

        // PMS Group: open, in_progress, waiting_for_customer, resolved, closed
        if ($pmsGroup) {
            $pmsGroup->ticketStatuses()->attach([
                $openStatus->id,
                $inProgressStatus->id,
                $waitingStatus->id,
                $resolvedStatus->id,
                $closedStatus->id,
            ]);
            $this->command->info("✅ PMS group configured with 5 statuses");
        }

        // Hardware Group: open, ready_for_dispatch, dispatched, closed
        if ($hardwareGroup) {
            // Create hardware-specific statuses if they don't exist
            $readyForDispatch = TicketStatus::firstOrCreate(
                ['key' => 'ready_for_dispatch'],
                [
                    'name' => 'Ready for Dispatch',
                    'description' => 'Hardware is ready to be dispatched to customer',
                    'color' => '#f59e0b',
                    'sort_order' => 6,
                    'is_protected' => false,
                    'is_active' => true,
                ]
            );

            $dispatched = TicketStatus::firstOrCreate(
                ['key' => 'dispatched'],
                [
                    'name' => 'Dispatched',
                    'description' => 'Hardware has been dispatched to customer',
                    'color' => '#8b5cf6',
                    'sort_order' => 7,
                    'is_protected' => false,
                    'is_active' => true,
                ]
            );

            $hardwareGroup->ticketStatuses()->attach([
                $openStatus->id,
                $readyForDispatch->id,
                $dispatched->id,
                $closedStatus->id,
            ]);
            $this->command->info("✅ Hardware group configured with 4 statuses");
        }

        // POS Group: open, in_progress, resolved, closed
        if ($posGroup) {
            $posGroup->ticketStatuses()->attach([
                $openStatus->id,
                $inProgressStatus->id,
                $resolvedStatus->id,
                $closedStatus->id,
            ]);
            $this->command->info("✅ POS group configured with 4 statuses");
        }

        // MC Group: open, in_progress, waiting_for_customer, resolved, closed
        if ($mcGroup) {
            $mcGroup->ticketStatuses()->attach([
                $openStatus->id,
                $inProgressStatus->id,
                $waitingStatus->id,
                $resolvedStatus->id,
                $closedStatus->id,
            ]);
            $this->command->info("✅ MC group configured with 5 statuses");
        }

        // Admin Group: All statuses (full access)
        if ($adminGroup) {
            $adminGroup->ticketStatuses()->attach(TicketStatus::all()->pluck('id')->toArray());
            $this->command->info("✅ Admin group configured with all statuses");
        }

        $this->command->info('🎉 Department group status access seeded successfully!');
    }
}
