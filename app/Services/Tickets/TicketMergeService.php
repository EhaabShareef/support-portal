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
     * @param array<int> $ticketIds
     */
    public function merge(array $ticketIds, int $actorId): Ticket
    {
        return DB::transaction(function () use ($ticketIds, $actorId) {
            // Select and lock the target tickets with deterministic ordering to prevent deadlocks
            // Order by ID to ensure consistent lock acquisition sequence and prevent deadlocks
            // The lockForUpdate() prevents concurrent message/note insertion during merge
            $tickets = Ticket::whereIn('id', $ticketIds)
                ->lockForUpdate()
                ->orderBy('id')
                ->get();
            
            // Validate the locked ticket collection
            if ($tickets->isEmpty()) {
                throw new DomainException('No tickets provided');
            }

            // Check if any tickets are already merged or are merge masters (with lock held)
            foreach ($tickets as $ticket) {
                if ($ticket->is_merged) {
                    throw new DomainException("Cannot merge ticket #{$ticket->id}: This ticket has already been merged into another ticket.");
                }
                if ($ticket->is_merged_master) {
                    throw new DomainException("Cannot merge ticket #{$ticket->id}: This ticket is a merge master and cannot be merged into another ticket.");
                }
            }

            // Select base ticket from original input order (first provided ID) while maintaining deterministic locking
            $baseTicketId = $ticketIds[0];
            $baseTicket = $tickets->firstWhere('id', $baseTicketId);
            
            if (!$baseTicket) {
                throw new DomainException("Base ticket #{$baseTicketId} not found in provided tickets");
            }

            // Validate organization consistency (with lock held)
            $orgId = $baseTicket->organization_id;
            if ($tickets->pluck('organization_id')->unique()->count() > 1) {
                throw new DomainException('Tickets must belong to the same organization');
            }
            
            $actor = User::findOrFail($actorId);
            $clientId = optional($baseTicket->organization->users()->whereHas('roles', fn($q) => $q->where('name','client'))->first())->id ?? $actorId;

            $newTicket = Ticket::create([
                'subject' => $baseTicket->subject,
                'status' => 'open',
                'priority' => $baseTicket->priority,
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'department_id' => $baseTicket->department_id,
                'is_merged_master' => true,
            ]);

            foreach ($tickets as $ticket) {
                TicketMessage::where('ticket_id', $ticket->id)->update(['ticket_id' => $newTicket->id]);
                TicketNote::where('ticket_id', $ticket->id)->where('is_internal', false)->update(['ticket_id' => $newTicket->id]);

                TicketMessage::create([
                    'ticket_id' => $newTicket->id,
                    'sender_id' => $actorId,
                    'message' => "Merged from #{$ticket->id} by {$actor->name}",
                    'is_system_message' => true,
                    'is_log' => false,
                ]);

                $ticket->update([
                    'is_merged' => true,
                    'merged_into_ticket_id' => $newTicket->id,
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

                TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'sender_id' => $actorId,
                    'message' => "This ticket was merged into #{$newTicket->id} by {$actor->name}",
                    'is_system_message' => true,
                    'is_log' => true,
                ]);
            }

            return $newTicket;
        });
    }
}
