<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\IncomingEmail;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketMessageAttachment;
use App\Models\User;
use App\Services\EmailParserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmailParserTest extends TestCase
{
    use RefreshDatabase;

    public function test_parses_incoming_email_and_reopens_ticket(): void
    {
        Role::create(['name' => 'client']);

        $organization = Organization::create([
            'name' => 'Org',
            'company' => 'Org Co',
            'company_contact' => 'Contact',
            'tin_no' => '123',
            'email' => 'org@example.com',
            'phone' => '123456',
            'subscription_status' => 'active',
        ]);

        $department = Department::create([
            'name' => 'Support',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $client = User::factory()->create(['organization_id' => $organization->id]);
        $client->assignRole('client');

        $ticket = Ticket::create([
            'subject' => 'Issue',
            'status' => 'closed',
            'priority' => 'normal',
            'organization_id' => $organization->id,
            'client_id' => $client->id,
            'department_id' => $department->id,
        ]);

        Storage::fake('local');

        $service = new EmailParserService();
        $emailData = [
            'message_id' => 'msg-1@example.com',
            'from' => 'newuser@example.com',
            'to' => 'ticket-'.$ticket->id.'@example.com',
            'subject' => "[TICKET-{$ticket->id}] Test reply",
            'body' => "Hello world\n--\nSignature\n> quoted text",
            'attachments' => [
                [
                    'filename' => 'test.png',
                    'content' => base64_encode('filecontent'),
                    'mime_type' => 'image/png',
                    'size' => 12,
                ],
            ],
        ];

        $result = $service->parseIncomingEmail($emailData);

        $this->assertTrue($result);
        $this->assertDatabaseHas('ticket_messages', ['ticket_id' => $ticket->id, 'message' => 'Hello world']);
        $this->assertDatabaseHas('ticket_message_attachments', ['original_name' => 'test.png']);
        $this->assertDatabaseHas('incoming_emails', ['message_id' => 'msg-1@example.com']);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'reopened']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('client'));
    }
}
