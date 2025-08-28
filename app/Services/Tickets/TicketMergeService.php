<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TicketMergeService
{
    /**
     * @param array<int> $ticketIds - First ID is the target ticket, others are source tickets
     */
    public function merge(array $ticketIds, int $actorId): Ticket
    {
        return DB::transaction(function () use ($ticketIds, $actorId) {
            // Select and lock the target tickets with deterministic ordering to prevent deadlocks
            $tickets = Ticket::whereIn('id', $ticketIds)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();
            
            // Validate the locked ticket collection
            if ($tickets->isEmpty()) {
                throw new DomainException('No tickets provided');
            }

            // The first ticket ID is the target ticket (where we merge into)
            $targetTicketId = $ticketIds[0];
            $targetTicket = $tickets->firstWhere('id', $targetTicketId);
            
            if (!$targetTicket) {
                throw new DomainException("Target ticket #{$targetTicketId} not found in provided tickets");
            }

            // Check if target ticket is already merged into another ticket (but allow merge masters as targets)
            if ($targetTicket->is_merged && !$targetTicket->is_merged_master) {
                throw new DomainException("Cannot merge into ticket #{$targetTicketId}: This ticket has already been merged into another ticket.");
            }

            // Get source tickets (all except the target)
            $sourceTickets = $tickets->where('id', '!=', $targetTicketId);

            // Validate organization consistency
            $orgId = $targetTicket->organization_id;
            if ($tickets->pluck('organization_id')->unique()->count() > 1) {
                throw new DomainException('Tickets must belong to the same organization');
            }

            // Check source tickets for merge restrictions
            foreach ($sourceTickets as $sourceTicket) {
                if ($sourceTicket->is_merged) {
                    throw new DomainException("Cannot merge ticket #{$sourceTicket->id}: This ticket has already been merged into another ticket.");
                }
                if ($sourceTicket->status === 'closed') {
                    throw new DomainException("Cannot merge ticket #{$sourceTicket->id}: This ticket is closed.");
                }
            }
            
            $actor = User::findOrFail($actorId);

            // Merge all source tickets into the target ticket
            foreach ($sourceTickets as $sourceTicket) {
                // Move messages from source to target
                TicketMessage::where('ticket_id', $sourceTicket->id)->update(['ticket_id' => $targetTicket->id]);
                
                // Move public notes from source to target
                TicketNote::where('ticket_id', $sourceTicket->id)->where('is_internal', false)->update(['ticket_id' => $targetTicket->id]);

                // Create merge message in target ticket
                TicketMessage::create([
                    'ticket_id' => $targetTicket->id,
                    'sender_id' => $actorId,
                    'message' => "Merged from #{$sourceTicket->ticket_number} by {$actor->name}",
                    'is_system_message' => true,
                    'is_log' => false,
                ]);

                // Mark source ticket as merged and close it
                $sourceTicket->update([
                    'is_merged' => true,
                    'merged_into_ticket_id' => $targetTicket->id,
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

                // Create merge message in source ticket
                TicketMessage::create([
                    'ticket_id' => $sourceTicket->id,
                    'sender_id' => $actorId,
                    'message' => "This ticket was merged into #{$targetTicket->ticket_number} by {$actor->name}",
                    'is_system_message' => true,
                    'is_log' => true,
                ]);
            }

            // Mark target ticket as merge master (can receive more merges)
            $targetTicket->update(['is_merged_master' => true]);

            // Refresh and return the target ticket
            return $targetTicket->fresh();
        });
    }
}
