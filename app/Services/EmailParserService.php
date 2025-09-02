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
use Illuminate\Support\Facades\DB;

class EmailParserService
{
    public function parseIncomingEmail(array $emailData): bool
    {
        $normalizedFrom = $this->normalizeEmailAddress($emailData['from'] ?? '');
        
        try {
            // Wrap all database operations in a transaction
            return DB::transaction(function () use ($emailData, $normalizedFrom) {
                // Normalize the 'to' field to handle both string and array inputs
                $toField = $emailData['to'] ?? null;
                if (is_array($toField)) {
                    $toField = implode(', ', array_filter($toField));
                }

                $incoming = IncomingEmail::create([
                    'message_id' => $emailData['message_id'] ?? Str::uuid(),
                    'from' => $normalizedFrom,
                    'to' => $toField,
                    'subject' => $emailData['subject'] ?? null,
                    'body' => $emailData['body'] ?? null,
                    'headers' => $emailData['headers'] ?? [],
                    'attachments' => $emailData['attachments'] ?? [],
                ]);

                $ticketId = $this->extractTicketId($emailData);
                $ticket = $ticketId ? Ticket::find($ticketId) : null;

                if (!$ticket) {
                    // Create a new ticket from email
                    $user = $this->findOrCreateUser($normalizedFrom);
                    $ticket = Ticket::create([
                        'subject' => $emailData['subject'] ?? 'Email Ticket',
                        'organization_id' => $user->organization_id ?? 1,
                        'client_id' => $user->id,
                        'department_id' => 1,
                        'priority' => 'normal',
                        'uuid' => Str::uuid(),
                        'ticket_number' => 'TK-' . date('Ym') . '-' . str_pad(Ticket::count() + 1, 4, '0', STR_PAD_LEFT),
                    ]);
                } else {
                    $user = $this->findOrCreateUser($normalizedFrom);
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
            });
        } catch (\Exception $e) {
            Log::error('Email parser failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function extractTicketId(array $emailData): ?int
    {
        // Check subject for ticket reference
        if (isset($emailData['subject']) && preg_match('/\[TICKET-(\d+)\]/', $emailData['subject'], $m)) {
            return (int) $m[1];
        }

        // Check reply_to address for secure token validation
        if (!empty($emailData['reply_to'])) {
            $emailReplyService = app(\App\Services\EmailReplyService::class);
            $ticketId = $emailReplyService->verifyReplyAddress($emailData['reply_to']);
            if ($ticketId !== null) {
                return $ticketId;
            }
        }

        // Check body for ticket reference
        if (isset($emailData['body']) && preg_match('/Ticket #(\d+)/', $emailData['body'], $m)) {
            return (int) $m[1];
        }

        return null;
    }

    private function findOrCreateUser(string $email): User
    {
        // Validate email format before processing
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email: ' . $email);
        }

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
        // Use safe defaults for configuration values
        $maxSize = (int) config('mail.email_parser.max_attachment_size', 5 * 1024 * 1024); // 5MB default
        $allowedExtensions = config('mail.email_parser.allowed_extensions', [
            'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip'
        ]);
        
        // Ensure allowedExtensions is always an array
        if (!is_array($allowedExtensions)) {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv', 'zip'];
        }
        
        // Normalize extension to lowercase
        $extension = strtolower(pathinfo($attachment['filename'] ?? '', PATHINFO_EXTENSION));
        
        // Check if extension is in allowed list
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }
        
        // Check size - treat zero or negative as unlimited
        $attachmentSize = (int) ($attachment['size'] ?? 0);
        if ($maxSize > 0 && $attachmentSize > $maxSize) {
            return false;
        }
        
        return true;
    }

    private function storeAttachment(array $attachment): array
    {
        // 1. Verify content is valid base64 and decode safely
        $content = $attachment['content'] ?? '';
        if (empty($content)) {
            throw new \InvalidArgumentException('Attachment content is empty');
        }

        // Validate base64 format and decode
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $content)) {
            throw new \InvalidArgumentException('Invalid base64 content format');
        }

        $data = base64_decode($content, true);
        if ($data === false) {
            throw new \InvalidArgumentException('Failed to decode base64 content');
        }

        // Check decoded content length
        $decodedSize = strlen($data);
        if ($decodedSize === 0) {
            throw new \InvalidArgumentException('Decoded content is empty');
        }

        // Check against maximum size limit
        $maxSize = config('mail.email_parser.max_attachment_size', 5 * 1024 * 1024); // 5MB default
        if ($maxSize > 0 && $decodedSize > $maxSize) {
            throw new \InvalidArgumentException("Attachment size {$decodedSize} exceeds maximum allowed size {$maxSize}");
        }

        // 2. Sanitize filename
        $originalFilename = $attachment['filename'] ?? 'file';
        $sanitizedFilename = $this->sanitizeFilename($originalFilename);

        // 3. Build safe storage path using UUID prefix
        $uuid = Str::uuid();
        $path = 'ticket_attachments/' . $uuid . '_' . $sanitizedFilename;

        // 4. Attempt to store the file and verify success
        $storageResult = Storage::disk('local')->put($path, $data);
        if (!$storageResult) {
            throw new \RuntimeException('Failed to store attachment file');
        }

        // 5. Determine actual file size and MIME type from stored content
        $actualSize = Storage::disk('local')->size($path);
        $mimeType = $this->detectMimeType($data, $sanitizedFilename);

        return [
            'path' => $path,
            'original_name' => $originalFilename,
            'stored_name' => $sanitizedFilename,
            'mime_type' => $mimeType,
            'size' => $actualSize,
            'disk' => 'local',
        ];
    }

    /**
     * Sanitize filename by removing dangerous characters and path traversal attempts
     * 
     * @param string $filename
     * @return string
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove null bytes, directory separators, and other dangerous characters
        $filename = str_replace(['\0', '/', '\\', '..', ':', '*', '?', '"', '<', '>', '|'], '', $filename);
        
        // Remove leading/trailing dots and spaces
        $filename = trim($filename, '. ');
        
        // If filename is empty after sanitization, use a safe default
        if (empty($filename)) {
            $filename = 'attachment';
        }
        
        // Limit filename length
        if (strlen($filename) > 100) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $filename = substr($name, 0, 100 - strlen($extension) - 1) . '.' . $extension;
        }
        
        return $filename;
    }

    /**
     * Detect MIME type from file content and extension
     * 
     * @param string $content
     * @param string $filename
     * @return string
     */
    private function detectMimeType(string $content, string $filename): string
    {
        // Try to detect MIME type from content using finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->buffer($content);
        
        // If finfo detection fails, fall back to extension-based detection
        if ($detectedMime === 'application/octet-stream' || $detectedMime === false) {
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $detectedMime = $this->getMimeTypeFromExtension($extension);
        }
        
        return $detectedMime ?: 'application/octet-stream';
    }

    /**
     * Get MIME type from file extension
     * 
     * @param string $extension
     * @return string
     */
    private function getMimeTypeFromExtension(string $extension): string
    {
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
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
        $username = Str::before($email, '@');
        $baseUsername = $username;
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Normalize an email address by extracting the actual email from display name format
     * and validating it as a proper email address
     * 
     * @param string|null $email
     * @return string
     */
    private function normalizeEmailAddress(?string $email): string
    {
        if (empty($email)) {
            return config('mail.email_parser.fallback_email', 'noreply@example.com');
        }

        // Extract email from "Display Name <email@domain>" format
        if (preg_match('/<(.+?)>/', $email, $matches)) {
            $email = $matches[1];
        }

        // Trim whitespace and quotes
        $email = trim($email, " \t\n\r\0\x0B\"'");

        // Validate email format
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return strtolower($email);
        }

        // If validation fails, try to extract from headers or use fallback
        Log::warning('Invalid email address detected, using fallback', [
            'original_email' => $email,
            'fallback' => config('mail.email_parser.fallback_email', 'noreply@example.com')
        ]);

        return config('mail.email_parser.fallback_email', 'noreply@example.com');
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
