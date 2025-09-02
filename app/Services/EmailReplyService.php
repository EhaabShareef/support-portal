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
        $domain = config('mail.email_domain', 'example.com');
        
        // Get the email secret for HMAC generation
        $secret = config('services.email_webhook.secret');
        if (!$secret) {
            throw new \RuntimeException('Email webhook secret not configured. Set EMAIL_WEBHOOK_SECRET in your .env file.');
        }
        
        // Generate HMAC token for ticket ID
        $token = $this->generateTicketToken($ticket->id, $secret);
        
        return "ticket-{$ticket->id}-{$token}@{$domain}";
    }

    /**
     * Generate a secure HMAC token for a ticket
     * 
     * @param int $ticketId
     * @param string $secret
     * @return string
     */
    private function generateTicketToken(int $ticketId, string $secret): string
    {
        // Compute HMAC using SHA256
        $hmac = hash_hmac('sha256', (string)$ticketId, $secret);
        
        // Return a truncated but collision-resistant token (first 16 characters)
        return substr($hmac, 0, 16);
    }

    /**
     * Verify a ticket token from a reply address
     * 
     * @param string $replyAddress
     * @return int|null Returns ticket ID if valid, null if invalid
     */
    public function verifyReplyAddress(string $replyAddress): ?int
    {
        $secret = config('services.email_webhook.secret');
        if (!$secret) {
            return null;
        }
        
        // Parse the reply address format: ticket-{id}-{token}@{domain}
        if (!preg_match('/^ticket-(\d+)-([a-f0-9]{16})@(.+)$/', $replyAddress, $matches)) {
            return null;
        }
        
        $ticketId = (int)$matches[1];
        $receivedToken = $matches[2];
        
        // Generate expected token
        $expectedToken = $this->generateTicketToken($ticketId, $secret);
        
        // Compare tokens securely
        if (hash_equals($expectedToken, $receivedToken)) {
            return $ticketId;
        }
        
        return null;
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
