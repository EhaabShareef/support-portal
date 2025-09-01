<?php

namespace App\Services;

use App\Models\IncomingEmail;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailParserService
{
    public function parseIncomingEmail(array $emailData): bool
    {
        $incoming = IncomingEmail::create([
            'message_id' => $emailData['message_id'] ?? Str::uuid(),
            'from' => $emailData['from'] ?? '',
            'to' => $emailData['to'] ?? null,
            'subject' => $emailData['subject'] ?? null,
            'body' => $emailData['body'] ?? null,
            'headers' => $emailData['headers'] ?? [],
            'attachments' => $emailData['attachments'] ?? [],
        ]);

        try {
            $ticketId = $this->extractTicketId($emailData);
            $ticket = $ticketId ? Ticket::find($ticketId) : null;

            if (!$ticket) {
                // Create a new ticket from email
                $user = $this->findOrCreateUser($emailData['from']);
                $ticket = Ticket::create([
                    'subject' => $emailData['subject'] ?? 'Email Ticket',
                    'description' => $emailData['body'] ?? '',
                    'organization_id' => $user->organization_id ?? 1,
                    'client_id' => $user->id,
                    'department_id' => 1,
                    'priority' => 'normal',
                ]);
            } else {
                $user = $this->findOrCreateUser($emailData['from']);
            }

            $incoming->update([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
            ]);

            $message = $this->parseEmailContent($emailData);
            $attachments = $this->processAttachments($emailData['attachments'] ?? []);

            $ticketMessage = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $user->id,
                'message' => $message,
                'is_internal' => false,
                'is_system_message' => false,
                'email_message_id' => $emailData['message_id'] ?? null,
                'email_in_reply_to' => $emailData['in_reply_to'] ?? null,
                'email_references' => $emailData['references'] ?? null,
                'email_headers' => $emailData['headers'] ?? null,
            ]);

            foreach ($attachments as $attachment) {
                $this->attachFileToMessage($ticketMessage, $attachment);
            }

            $this->updateTicketStatus($ticket);

            $incoming->update(['processed_at' => now()]);

            Log::info('Email parser processed email', [
                'ticket_id' => $ticket->id,
                'message_id' => $ticketMessage->id,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Email parser failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function extractTicketId(array $emailData): ?int
    {
        if (isset($emailData['subject']) && preg_match('/\[TICKET-(\d+)\]/', $emailData['subject'], $m)) {
            return (int) $m[1];
        }

        if (!empty($emailData['reply_to']) && preg_match('/ticket-(\d+)@/', $emailData['reply_to'], $m)) {
            return (int) $m[1];
        }

        if (isset($emailData['body']) && preg_match('/Ticket #(\d+)/', $emailData['body'], $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function findOrCreateUser(string $email): User
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = User::create([
                'name' => $this->extractNameFromEmail($email),
                'email' => $email,
                'username' => $this->generateUsernameFromEmail($email),
                'password' => bcrypt(Str::random(32)),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('client');
        }
        return $user;
    }

    private function parseEmailContent(array $emailData): string
    {
        $body = $emailData['body'] ?? '';
        $body = $this->removeEmailSignature($body);
        $body = $this->removeQuotedText($body);
        $body = $this->cleanEmailFormatting($body);
        return trim($body);
    }

    private function processAttachments(array $attachments): array
    {
        $processed = [];
        foreach ($attachments as $attachment) {
            if ($this->isValidAttachment($attachment)) {
                $processed[] = $this->storeAttachment($attachment);
            }
        }
        return $processed;
    }

    private function isValidAttachment(array $attachment): bool
    {
        $max = config('mail.email_parser.max_attachment_size');
        $allowed = config('mail.email_parser.allowed_extensions', []);
        $ext = strtolower(pathinfo($attachment['filename'] ?? '', PATHINFO_EXTENSION));
        return ($attachment['size'] ?? 0) <= $max && in_array($ext, $allowed);
    }

    private function storeAttachment(array $attachment): array
    {
        $data = base64_decode($attachment['content'] ?? '');
        $path = 'ticket_attachments/' . Str::uuid() . '_' . ($attachment['filename'] ?? 'file');
        Storage::disk('local')->put($path, $data);
        return [
            'path' => $path,
            'original_name' => $attachment['filename'] ?? 'file',
            'mime_type' => $attachment['mime_type'] ?? null,
            'size' => $attachment['size'] ?? strlen($data),
            'disk' => 'local',
        ];
    }

    private function attachFileToMessage(TicketMessage $message, array $file): void
    {
        TicketMessageAttachment::create([
            'ticket_message_id' => $message->id,
            'disk' => $file['disk'],
            'path' => $file['path'],
            'original_name' => $file['original_name'],
            'mime_type' => $file['mime_type'],
            'size' => $file['size'],
        ]);
    }

    private function updateTicketStatus(Ticket $ticket): void
    {
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'reopened']);
        }
        $ticket->update(['latest_message_at' => now()]);
    }

    private function extractNameFromEmail(string $email): string
    {
        return Str::title(Str::before($email, '@'));
    }

    private function generateUsernameFromEmail(string $email): string
    {
        return Str::before($email, '@') . Str::random(5);
    }

    private function removeEmailSignature(string $body): string
    {
        $parts = preg_split('/\n--\n/', $body);
        return $parts[0] ?? $body;
    }

    private function removeQuotedText(string $body): string
    {
        $lines = array_filter(explode("\n", $body), fn($line) => !Str::startsWith(trim($line), '>'));
        return implode("\n", $lines);
    }

    private function cleanEmailFormatting(string $body): string
    {
        return trim(strip_tags($body));
    }
}
