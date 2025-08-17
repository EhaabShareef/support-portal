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
        $tickets = Ticket::whereIn('id', $ticketIds)->get();
        if ($tickets->isEmpty()) {
            throw new DomainException('No tickets provided');
        }

        $orgId = $tickets->first()->organization_id;
        if ($tickets->pluck('organization_id')->unique()->count() > 1) {
            throw new DomainException('Tickets must belong to the same organization');
        }

        return DB::transaction(function () use ($tickets, $orgId, $actorId) {
            $actor = User::findOrFail($actorId);
            $clientId = optional($tickets->first()->organization->users()->whereHas('roles', fn($q) => $q->where('name','client'))->first())->id ?? $actorId;

            $newTicket = Ticket::create([
                'subject' => $tickets->first()->subject,
                'status' => 'open',
                'priority' => $tickets->first()->priority,
                'organization_id' => $orgId,
                'client_id' => $clientId,
                'department_id' => $tickets->first()->department_id,
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
