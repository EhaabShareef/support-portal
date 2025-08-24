<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use App\Services\Tickets\TicketSplitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketSplitServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_splits_ticket_and_moves_messages()
    {
        $org = Organization::create([
            'name' => 'Org',
            'company' => 'Org Co',
            'company_contact' => 'Contact',
            'tin_no' => '123',
            'email' => 'org@example.com',
            'phone' => '123',
        ]);

        $dept = Department::create(['name' => 'Support']);

        $client = User::factory()->create();
        $actor = User::factory()->create();

        $ticket = Ticket::create([
            'subject' => 'Original',
            'status' => 'open',
            'priority' => 'normal',
            'organization_id' => $org->id,
            'client_id' => $client->id,
            'department_id' => $dept->id,
        ]);

        $m1 = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $client->id,
            'message' => 'First',
        ]);

        $m2 = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $client->id,
            'message' => 'Second',
        ]);

        TicketNote::create([
            'ticket_id' => $ticket->id,
            'note' => 'Note',
            'user_id' => $client->id,
            'is_internal' => false,
        ]);

        $service = new TicketSplitService();
        $new = $service->split($ticket, $m2->id, ['close_original' => true, 'copy_notes' => true], $actor);

        $this->assertDatabaseHas('tickets', [
            'id' => $new->id,
            'split_from_ticket_id' => $ticket->id,
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);

        $this->assertEquals(2, TicketMessage::where('ticket_id', $new->id)->count());
        $this->assertEquals(2, TicketMessage::where('ticket_id', $ticket->id)->count());
        $this->assertEquals(1, TicketNote::where('ticket_id', $new->id)->count());
    }
}
