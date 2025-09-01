<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailReplyService
{
    public function generateReplyAddress(Ticket $ticket): string
    {
        $domain = config('app.email_domain', 'example.com');
        return "ticket-{$ticket->id}@{$domain}";
    }

    public function sendTicketNotification(Ticket $ticket, TicketMessage $message): void
    {
        if (!$ticket->client) {
            return;
        }

        $replyAddress = $this->generateReplyAddress($ticket);

        Mail::send('emails.ticket-notification', [
            'ticket' => $ticket,
            'message' => $message,
            'replyAddress' => $replyAddress,
        ], function ($mail) use ($ticket, $message, $replyAddress) {
            $mail->to($ticket->client->email)
                ->subject("[TICKET-{$ticket->id}] {$ticket->subject}")
                ->replyTo($replyAddress);
        });
    }

    public function sendTicketUpdate(Ticket $ticket, string $updateType, array $data = []): void
    {
        if (!$ticket->client) {
            return;
        }

        $replyAddress = $this->generateReplyAddress($ticket);

        Mail::send('emails.ticket-update', [
            'ticket' => $ticket,
            'updateType' => $updateType,
            'data' => $data,
            'replyAddress' => $replyAddress,
        ], function ($mail) use ($ticket, $updateType, $replyAddress) {
            $mail->to($ticket->client->email)
                ->subject("[TICKET-{$ticket->id}] Ticket {$updateType}")
                ->replyTo($replyAddress);
        });
    }
}
