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
     * @param array<string, mixed> $mergeOptions - Options for how to handle the merge
     */
    public function merge(array $ticketIds, int $actorId, array $mergeOptions = []): Ticket
    {
        return DB::transaction(function () use ($ticketIds, $actorId, $mergeOptions) {
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

            // Get source tickets (all except the target)
            $sourceTickets = $tickets->where('id', '!=', $targetTicketId);

            // Validate organization consistency
            $orgId = $targetTicket->organization_id;
            if ($tickets->pluck('organization_id')->unique()->count() > 1) {
                throw new DomainException('Tickets must belong to the same organization. Cross-organization merging is not allowed.');
            }

            // Check source tickets for basic restrictions (only closed status check)
            foreach ($sourceTickets as $sourceTicket) {
                if ($sourceTicket->status === 'closed') {
                    throw new DomainException("Cannot merge ticket #{$sourceTicket->id}: This ticket is closed.");
                }
            }
            
            $actor = User::findOrFail($actorId);

            // Handle attribute merging based on options
            $this->mergeTicketAttributes($targetTicket, $sourceTickets, $mergeOptions);

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

    /**
     * Merge ticket attributes based on user preferences
     */
    private function mergeTicketAttributes(Ticket $targetTicket, $sourceTickets, array $options): void
    {
        $preservePriority = $options['preserve_priority'] ?? false;
        $preserveStatus = $options['preserve_status'] ?? false;
        $combineSubjects = $options['combine_subjects'] ?? false;
        $preserveOwner = $options['preserve_owner'] ?? false;

        // Handle priority merging
        if (!$preservePriority) {
            $highestPriority = $this->getHighestPriority($targetTicket, $sourceTickets);
            if ($highestPriority !== $targetTicket->priority) {
                $targetTicket->priority = $highestPriority;
            }
        }

        // Handle status merging
        if (!$preserveStatus) {
            $bestStatus = $this->getBestStatus($targetTicket, $sourceTickets);
            if ($bestStatus !== $targetTicket->status) {
                $targetTicket->status = $bestStatus;
            }
        }

        // Handle subject combining
        if ($combineSubjects) {
            $combinedSubject = $this->combineSubjects($targetTicket, $sourceTickets);
            $targetTicket->subject = $combinedSubject;
        }

        // Handle owner preservation
        if (!$preserveOwner && !$targetTicket->owner_id) {
            $firstOwner = $sourceTickets->firstWhere('owner_id', '!=', null);
            if ($firstOwner) {
                $targetTicket->owner_id = $firstOwner->owner_id;
            }
        }

        // Save the updated attributes
        $targetTicket->save();
    }

    /**
     * Get the highest priority from all tickets
     */
    private function getHighestPriority(Ticket $targetTicket, $sourceTickets): string
    {
        $priorities = collect([$targetTicket->priority])
            ->merge($sourceTickets->pluck('priority'))
            ->filter();

        $priorityOrder = ['low' => 1, 'normal' => 2, 'high' => 3, 'urgent' => 4, 'critical' => 5];
        
        $highestPriority = $priorities->sortBy(function ($priority) use ($priorityOrder) {
            return $priorityOrder[$priority] ?? 0;
        })->last();

        return $highestPriority ?: 'normal';
    }

    /**
     * Get the best status from all tickets
     */
    private function getBestStatus(Ticket $targetTicket, $sourceTickets): string
    {
        $statuses = collect([$targetTicket->status])
            ->merge($sourceTickets->pluck('status'))
            ->filter();

        // Priority: open > in_progress > resolved > closed
        $statusOrder = ['closed' => 1, 'resolved' => 2, 'in_progress' => 3, 'open' => 4];
        
        $bestStatus = $statuses->sortBy(function ($status) use ($statusOrder) {
            return $statusOrder[$status] ?? 0;
        })->last();

        return $bestStatus ?: 'open';
    }

    /**
     * Combine subjects intelligently
     */
    private function combineSubjects(Ticket $targetTicket, $sourceTickets): string
    {
        $subjects = collect([$targetTicket->subject])
            ->merge($sourceTickets->pluck('subject'))
            ->filter()
            ->unique();

        if ($subjects->count() === 1) {
            return $subjects->first();
        }

        // If subjects are different, combine them
        $mainSubject = $targetTicket->subject;
        $additionalSubjects = $subjects->where('subject', '!=', $mainSubject)->take(2);
        
        if ($additionalSubjects->isNotEmpty()) {
            $combined = $mainSubject . ' (Merged with: ' . $additionalSubjects->implode(', ') . ')';
            // Limit length to avoid database issues
            return substr($combined, 0, 255);
        }

        return $mainSubject;
    }
}
