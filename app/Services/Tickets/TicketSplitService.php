<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TicketSplitService
{
    /**
     * @param array<string, mixed> $options
     */
    public function split(Ticket $source, int $startMessageId, array $options, User $actor): Ticket
    {
        return DB::transaction(function () use ($source, $startMessageId, $options, $actor) {
            $message = $source->messages()->where('id', $startMessageId)->first();
            if (! $message) {
                throw new DomainException('Invalid start message.');
            }

            $newTicket = Ticket::create([
                'subject' => 'Split: ' . $source->subject,
                'status' => 'open',
                'priority' => $source->priority,
                'organization_id' => $source->organization_id,
                'client_id' => $source->client_id,
                'department_id' => $source->department_id,
                'owner_id' => null,
                'split_from_ticket_id' => $source->id,
            ]);

            DB::table('ticket_messages')
                ->where('ticket_id', $source->id)
                ->where('id', '>=', $startMessageId)
                ->update(['ticket_id' => $newTicket->id]);

            if (! empty($options['copy_notes'])) {
                $source->notes()->where('is_internal', false)->get()->each(function ($note) use ($newTicket) {
                    $replica = $note->replicate();
                    $replica->ticket_id = $newTicket->id;
                    $replica->save();
                });
            }

            if (! empty($options['close_original'])) {
                $source->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);
            }

            TicketMessage::create([
                'ticket_id' => $newTicket->id,
                'sender_id' => $actor->id,
                'message' => "This ticket was split from #{$source->id} starting at message #{$startMessageId} by {$actor->name}.",
                'is_system_message' => true,
                'is_log' => false,
            ]);

            TicketMessage::create([
                'ticket_id' => $source->id,
                'sender_id' => $actor->id,
                'message' => "Messages from #{$startMessageId} onward were split into #{$newTicket->id} by {$actor->name}.",
                'is_system_message' => true,
                'is_log' => true,
            ]);

            $newLatest = DB::table('ticket_messages')->where('ticket_id', $newTicket->id)->max('created_at');
            $sourceLatest = DB::table('ticket_messages')->where('ticket_id', $source->id)->max('created_at');

            $newTicket->update(['latest_message_at' => $newLatest]);
            $source->update(['latest_message_at' => $sourceLatest]);

            return $newTicket;
        });
    }
}
