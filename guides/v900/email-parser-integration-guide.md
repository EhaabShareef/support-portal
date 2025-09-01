# Email Parser Integration Guide

## v9.0.0 - Ticket Messaging Email Integration

### 📧 **Overview**

This guide outlines the implementation of an email parser system that integrates seamlessly with your existing ticket messaging infrastructure. The system will allow users to reply to tickets via email, with emails being automatically parsed and converted into ticket messages.

### 🏗️ **Current System Analysis**

#### **Existing Ticket Messaging Structure**

- **TicketMessage Model**: Handles all ticket communications
- **Fields**: `ticket_id`, `sender_id`, `message`, `is_internal`, `is_system_message`, `is_log`, `metadata`
- **Attachments**: Support for file attachments via `TicketMessageAttachment`
- **Livewire Components**: `ReplyForm`, `ConversationThread`, `ViewTicket`

#### **Current Ticket Creation Flow**

- **CreateTicket Component**: Multi-step ticket creation process
- **Required Fields**: `subject`, `description`, `organization_id`, `client_id`, `department_id`, `priority`
- **Hardware Integration**: Links hardware items to tickets
- **Status Management**: Dynamic status system with workflow support

### 🎯 **Implementation Strategy**

#### **Phase 1: Email Infrastructure Setup (Cost-Free Testing)**

##### **1.1 Email Configuration**

```php
// config/mail.php - Add email parser configuration
'email_parser' => [
    'enabled' => env('EMAIL_PARSER_ENABLED', false),
    'incoming_mailbox' => env('EMAIL_PARSER_MAILBOX', 'support@hospitalitytechnology.com.mv'),
    'reply_prefix' => env('EMAIL_PARSER_REPLY_PREFIX', '[TICKET-'),
    'max_attachment_size' => env('EMAIL_PARSER_MAX_ATTACHMENT', 10240), // 10MB
    'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'gif'],
],
```

##### **1.2 Database Schema Extensions**

```sql
-- Add email tracking to tickets table
ALTER TABLE tickets ADD COLUMN email_thread_id VARCHAR(255) NULL;
ALTER TABLE tickets ADD COLUMN email_reply_address VARCHAR(255) NULL;
ALTER TABLE tickets ADD INDEX idx_tickets_email_thread (email_thread_id);

-- Add email metadata to ticket_messages table
ALTER TABLE ticket_messages ADD COLUMN email_message_id VARCHAR(255) NULL;
ALTER TABLE ticket_messages ADD COLUMN email_in_reply_to VARCHAR(255) NULL;
ALTER TABLE ticket_messages ADD COLUMN email_references TEXT NULL;
ALTER TABLE ticket_messages ADD COLUMN email_headers JSON NULL;
```

##### **1.3 Email Parser Service**

```php
// app/Services/EmailParserService.php
<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EmailParserService
{
    public function parseIncomingEmail(array $emailData): bool
    {
        try {
            // Extract ticket ID from subject or reply-to
            $ticketId = $this->extractTicketId($emailData);
            
            if (!$ticketId) {
                Log::warning('Email parser: No ticket ID found in email', $emailData);
                return false;
            }
            
            $ticket = Ticket::find($ticketId);
            if (!$ticket) {
                Log::warning('Email parser: Ticket not found', ['ticket_id' => $ticketId]);
                return false;
            }
            
            // Find or create user from email
            $user = $this->findOrCreateUser($emailData['from']);
            
            // Parse email content
            $message = $this->parseEmailContent($emailData);
            
            // Process attachments
            $attachments = $this->processAttachments($emailData['attachments'] ?? []);
            
            // Create ticket message
            $ticketMessage = TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $user->id,
                'message' => $message,
                'is_internal' => false,
                'is_system_message' => false,
                'email_message_id' => $emailData['message_id'],
                'email_in_reply_to' => $emailData['in_reply_to'],
                'email_references' => $emailData['references'],
                'email_headers' => $emailData['headers'],
            ]);
            
            // Attach files
            foreach ($attachments as $attachment) {
                $this->attachFileToMessage($ticketMessage, $attachment);
            }
            
            // Update ticket status if needed
            $this->updateTicketStatus($ticket, $emailData);
            
            Log::info('Email parser: Successfully processed email', [
                'ticket_id' => $ticket->id,
                'message_id' => $ticketMessage->id,
                'user_id' => $user->id
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Email parser: Failed to process email', [
                'error' => $e->getMessage(),
                'email_data' => $emailData
            ]);
            return false;
        }
    }
    
    private function extractTicketId(array $emailData): ?int
    {
        // Method 1: Extract from subject line [TICKET-12345]
        if (preg_match('/\[TICKET-(\d+)\]/', $emailData['subject'], $matches)) {
            return (int) $matches[1];
        }
        
        // Method 2: Extract from reply-to header
        if (isset($emailData['reply_to'])) {
            if (preg_match('/ticket-(\d+)@/', $emailData['reply_to'], $matches)) {
                return (int) $matches[1];
            }
        }
        
        // Method 3: Extract from message body
        if (preg_match('/Ticket #(\d+)/', $emailData['body'], $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }
    
    private function findOrCreateUser(string $email): User
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            // Create a temporary user for external email senders
            $user = User::create([
                'name' => $this->extractNameFromEmail($email),
                'email' => $email,
                'username' => $this->generateUsernameFromEmail($email),
                'password' => bcrypt(str_random(32)), // Random password
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            
            // Assign client role
            $user->assignRole('client');
        }
        
        return $user;
    }
    
    private function parseEmailContent(array $emailData): string
    {
        $body = $emailData['body'];
        
        // Remove email signatures
        $body = $this->removeEmailSignature($body);
        
        // Remove quoted text (previous messages)
        $body = $this->removeQuotedText($body);
        
        // Clean up formatting
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
    
    private function updateTicketStatus(Ticket $ticket, array $emailData): void
    {
        // Auto-reopen closed tickets when email is received
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'reopened']);
        }
        
        // Update latest message timestamp
        $ticket->update(['latest_message_at' => now()]);
    }
}
```

#### **Phase 2: Email Webhook Endpoint (Free Testing)**

##### **2.1 Webhook Controller**

```php
// app/Http/Controllers/EmailWebhookController.php
<?php

namespace App\Http\Controllers;

use App\Services\EmailParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailWebhookController extends Controller
{
    public function __construct(
        private EmailParserService $emailParser
    ) {}
    
    public function handleIncomingEmail(Request $request)
    {
        // Verify webhook signature (implement based on your email service)
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        try {
            $emailData = $this->parseWebhookData($request);
            
            $success = $this->emailParser->parseIncomingEmail($emailData);
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Email processed successfully' : 'Failed to process email'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Email webhook error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    
    private function verifyWebhookSignature(Request $request): bool
    {
        // Implement signature verification based on your email service
        // For testing, you can temporarily return true
        return true;
    }
    
    private function parseWebhookData(Request $request): array
    {
        // Parse webhook data based on your email service format
        // This is a generic example - adapt to your service
        
        return [
            'message_id' => $request->input('message_id'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'in_reply_to' => $request->input('in_reply_to'),
            'references' => $request->input('references'),
            'headers' => $request->input('headers'),
            'attachments' => $request->input('attachments', []),
        ];
    }
}
```

##### **2.2 Webhook Route**

```php
// routes/web.php - Add to existing routes
Route::post('/webhooks/email', [EmailWebhookController::class, 'handleIncomingEmail'])
    ->name('webhooks.email')
    ->withoutMiddleware(['auth', 'csrf']); // Allow unauthenticated access for webhooks
```

#### **Phase 3: Email Reply Generation**

##### **3.1 Email Reply Service**

```php
// app/Services/EmailReplyService.php
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
        $domain = config('app.email_domain', 'hospitalitytechnology.com.mv');
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
```

##### **3.2 Email Templates**

```blade
{{-- resources/views/emails/ticket-notification.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket Update - {{ $ticket->ticket_number }}</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Ticket Update: {{ $ticket->subject }}</h2>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Ticket Number:</strong> {{ $ticket->ticket_number }}</p>
            <p><strong>Status:</strong> {{ ucfirst($ticket->status) }}</p>
            <p><strong>Priority:</strong> {{ ucfirst($ticket->priority) }}</p>
        </div>
        
        <div style="margin: 20px 0;">
            <h3>New Message:</h3>
            <div style="background: #fff; padding: 15px; border-left: 4px solid #007cba;">
                {!! nl2br(e($message->message)) !!}
            </div>
        </div>
        
        <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3>Reply to this ticket:</h3>
            <p>Simply reply to this email to add a message to the ticket. Your reply will be automatically added to the conversation.</p>
            <p><strong>Reply Address:</strong> {{ $replyAddress }}</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0; color: #666;">
            <p>This is an automated message from your support system.</p>
            <p>Do not reply to this email address directly.</p>
        </div>
    </div>
</body>
</html>
```

#### **Phase 4: Integration with Existing Components**

##### **4.1 Update ViewTicket Component**

```php
// app/Livewire/Tickets/ViewTicket.php - Add email integration
public function mount(Ticket $ticket): void
{
    // ... existing code ...
    
    // Generate email reply address
    $this->emailReplyAddress = app(EmailReplyService::class)->generateReplyAddress($ticket);
}

public function sendEmailNotification(): void
{
    $latestMessage = $this->ticket->messages()->latest()->first();
    
    if ($latestMessage) {
        app(EmailReplyService::class)->sendTicketNotification($this->ticket, $latestMessage);
        session()->flash('message', 'Email notification sent successfully.');
    }
}
```

##### **4.2 Update Ticket Model**

```php
// app/Models/Ticket.php - Add email integration
protected $fillable = [
    // ... existing fields ...
    'email_thread_id',
    'email_reply_address',
];

public function getEmailReplyAddressAttribute(): string
{
    if (!$this->attributes['email_reply_address']) {
        $this->attributes['email_reply_address'] = app(EmailReplyService::class)->generateReplyAddress($this);
        $this->save();
    }
    
    return $this->attributes['email_reply_address'];
}
```

### 🧪 **Testing Strategy (Cost-Free)**

#### **5.1 Local Testing Setup**

```bash
# Install MailHog for local email testing
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog

# Configure .env for local testing
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```

#### **5.2 Test Email Generation**

```php
// Create a test command
// app/Console/Commands/TestEmailParser.php
<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\EmailParserService;
use Illuminate\Console\Command;

class TestEmailParser extends Command
{
    protected $signature = 'test:email-parser {ticket_id}';
    protected $description = 'Test email parser with a specific ticket';
    
    public function handle()
    {
        $ticketId = $this->argument('ticket_id');
        $ticket = Ticket::find($ticketId);
        
        if (!$ticket) {
            $this->error("Ticket {$ticketId} not found");
            return;
        }
        
        // Simulate incoming email data
        $emailData = [
            'message_id' => 'test-' . time() . '@example.com',
            'from' => 'test@example.com',
            'to' => $ticket->email_reply_address,
            'subject' => "[TICKET-{$ticket->id}] Test email reply",
            'body' => "This is a test email reply to ticket {$ticket->ticket_number}.",
            'in_reply_to' => null,
            'references' => null,
            'headers' => [],
            'attachments' => [],
        ];
        
        $emailParser = app(EmailParserService::class);
        $success = $emailParser->parseIncomingEmail($emailData);
        
        if ($success) {
            $this->info("Email parser test successful for ticket {$ticket->ticket_number}");
        } else {
            $this->error("Email parser test failed for ticket {$ticket->ticket_number}");
        }
    }
}
```

#### **5.3 Webhook Testing**

```bash
# Test webhook endpoint locally
curl -X POST http://localhost:8000/webhooks/email \
  -H "Content-Type: application/json" \
  -d '{
    "message_id": "test-123@example.com",
    "from": "test@example.com",
    "to": "ticket-1@hospitalitytechnology.com.mv",
    "subject": "[TICKET-1] Test reply",
    "body": "This is a test email reply.",
    "attachments": []
  }'
```

### 🔧 **Implementation Steps**

#### **Step 1: Database Migration**

```bash
php artisan make:migration add_email_fields_to_tickets_and_messages
```

#### **Step 2: Create Services**

```bash
php artisan make:service EmailParserService
php artisan make:service EmailReplyService
```

#### **Step 3: Create Controller**

```bash
php artisan make:controller EmailWebhookController
```

#### **Step 4: Create Email Templates**

```bash
mkdir -p resources/views/emails
```

#### **Step 5: Test Integration**

```bash
php artisan test:email-parser 1
```

### 📋 **Configuration Options**

#### **Environment Variables**

```env
# Email Parser Configuration
EMAIL_PARSER_ENABLED=true
EMAIL_PARSER_MAILBOX=support@hospitalitytechnology.com.mv
EMAIL_PARSER_REPLY_PREFIX=[TICKET-
EMAIL_PARSER_MAX_ATTACHMENT=10240
EMAIL_PARSER_ALLOWED_EXTENSIONS=pdf,doc,docx,xls,xlsx,png,jpg,jpeg,gif

# Email Service Configuration
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=support@hospitalitytechnology.com.mv
MAIL_FROM_NAME="Hospitality Technology Support"
```

### 🚀 **Deployment Considerations**

#### **Production Setup**

1. **Email Service Integration**: Choose between SendGrid, Mailgun, AWS SES, or similar
2. **Webhook Security**: Implement proper signature verification
3. **Rate Limiting**: Add rate limiting to webhook endpoints
4. **Error Handling**: Implement comprehensive error logging and monitoring
5. **Queue Processing**: Use Laravel queues for email processing

#### **Monitoring & Logging**

```php
// Add to EmailParserService
Log::channel('email_parser')->info('Email processed', [
    'ticket_id' => $ticket->id,
    'message_id' => $ticketMessage->id,
    'processing_time' => $processingTime,
]);
```

### 📊 **Benefits**

1. **Seamless Integration**: Works with existing ticket messaging system
2. **Cost-Effective**: Uses existing infrastructure
3. **User-Friendly**: Clients can reply via email without logging in
4. **Automated**: Reduces manual ticket management
5. **Scalable**: Can handle high email volumes
6. **Audit Trail**: Maintains complete conversation history

### 🔒 **Security Considerations**

1. **Email Validation**: Verify sender authenticity
2. **Attachment Scanning**: Scan attachments for malware
3. **Rate Limiting**: Prevent email spam/abuse
4. **Access Control**: Ensure only authorized users can send emails
5. **Data Privacy**: Handle sensitive information appropriately

This implementation provides a robust, cost-effective email parser that integrates seamlessly with your existing ticket system while maintaining security and scalability.
