<?php

namespace Tests\Feature\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use App\Models\DepartmentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ViewTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test department structure
        $departmentGroup = DepartmentGroup::factory()->create(['name' => 'IT Support']);
        $department = Department::factory()->create([
            'name' => 'Technical Support',
            'department_group_id' => $departmentGroup->id
        ]);
        
        // Create test organization
        $organization = Organization::factory()->create(['name' => 'Test Org']);
        
        // Create test users
        $this->admin = User::factory()->create(['department_id' => $department->id]);
        $this->admin->assignRole('admin');
        
        $this->support = User::factory()->create(['department_id' => $department->id]);
        $this->support->assignRole('support');
        
        $this->client = User::factory()->create(['organization_id' => $organization->id]);
        $this->client->assignRole('client');
        
        // Create test ticket
        $this->ticket = Ticket::factory()->create([
            'organization_id' => $organization->id,
            'client_id' => $this->client->id,
            'department_id' => $department->id,
            'subject' => 'Test Support Ticket',
            'status' => 'open',
            'priority' => 'normal'
        ]);
    }

    /** @test */
    public function test_header_shows_badges_and_actions_based_on_permissions()
    {
        $this->actingAs($this->admin);
        
        $component = Livewire::test('tickets.view-ticket', ['ticket' => $this->ticket]);
        
        // Check that header shows subject and ticket number
        $component->assertSee($this->ticket->subject)
                  ->assertSee('Ticket #' . $this->ticket->ticket_number);
        
        // Check that badges are displayed
        $component->assertSee('open') // status badge
                  ->assertSee('normal'); // priority badge
        
        // Check that quick actions are available for admin
        $component->assertSeeLivewire('tickets.quick-actions');
    }

    /** @test */
    public function test_thread_refreshes_after_reply_note_close_reopen()
    {
        $this->actingAs($this->admin);
        
        // Test reply
        $replyComponent = Livewire::test('tickets.reply-form', ['ticket' => $this->ticket]);
        $replyComponent->set('replyMessage', 'Test reply message')
                      ->set('replyStatus', 'in_progress')
                      ->call('sendMessage');
        
        // Verify reply was added
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $this->ticket->id,
            'message' => 'Test reply message',
            'is_system_message' => false
        ]);
        
        // Test note
        $noteComponent = Livewire::test('tickets.note-form', ['ticket' => $this->ticket]);
        $noteComponent->set('note', 'Test internal note')
                     ->set('noteInternal', true)
                     ->call('addNote');
        
        // Verify note was added
        $this->assertDatabaseHas('ticket_notes', [
            'ticket_id' => $this->ticket->id,
            'note' => 'Test internal note',
            'is_internal' => true
        ]);
        
        // Test close
        $closeComponent = Livewire::test('tickets.close-modal', ['ticket' => $this->ticket]);
        $closeComponent->set('remarks', 'Closing ticket - resolved')
                      ->call('closeTicket');
        
        // Verify ticket was closed
        $this->ticket->refresh();
        $this->assertEquals('closed', $this->ticket->status);
        $this->assertNotNull($this->ticket->closed_at);
        
        // Test reopen
        $reopenComponent = Livewire::test('tickets.reopen-modal', ['ticket' => $this->ticket]);
        $reopenComponent->set('reason', 'Reopening for additional work')
                       ->call('reopenTicket');
        
        // Verify ticket was reopened
        $this->ticket->refresh();
        $this->assertEquals('open', $this->ticket->status);
        $this->assertNull($this->ticket->closed_at);
    }

    /** @test */
    public function test_internal_notes_hidden_from_clients_and_public_notes_in_thread()
    {
        // Create internal and public notes
        $this->ticket->notes()->create([
            'user_id' => $this->admin->id,
            'note' => 'Internal admin note',
            'is_internal' => true
        ]);
        
        $this->ticket->notes()->create([
            'user_id' => $this->admin->id,
            'note' => 'Public note visible to client',
            'is_internal' => false
        ]);
        
        // Test as admin - should see both notes
        $this->actingAs($this->admin);
        $adminComponent = Livewire::test('tickets.view-ticket', ['ticket' => $this->ticket]);
        $adminComponent->assertSee('Internal admin note')
                      ->assertSee('Public note visible to client');
        
        // Test as client - should only see public note
        $this->actingAs($this->client);
        $clientComponent = Livewire::test('tickets.view-ticket', ['ticket' => $this->ticket]);
        $clientComponent->assertDontSee('Internal admin note')
                       ->assertSee('Public note visible to client');
    }

    /** @test */
    public function test_conversation_thread_displays_messages_and_notes()
    {
        $this->actingAs($this->admin);
        
        // Create test message
        $this->ticket->messages()->create([
            'sender_id' => $this->admin->id,
            'message' => 'Test message in conversation',
            'is_system_message' => false
        ]);
        
        // Create public note
        $this->ticket->notes()->create([
            'user_id' => $this->admin->id,
            'note' => 'Public note in conversation',
            'is_internal' => false
        ]);
        
        $threadComponent = Livewire::test('tickets.conversation-thread', ['ticket' => $this->ticket]);
        
        // Should see both message and public note in conversation
        $threadComponent->assertSee('Test message in conversation')
                       ->assertSee('Public note in conversation');
    }

    /** @test */
    public function test_organization_note_visibility()
    {
        // Add notes to organization
        $this->ticket->organization->update(['notes' => 'Important organization notes']);
        
        $this->actingAs($this->admin);
        $component = Livewire::test('tickets.view-ticket', ['ticket' => $this->ticket]);
        
        // Should see organization notes
        $component->assertSee('Important organization notes');
    }

    /** @test */
    public function test_quick_actions_authorization()
    {
        // Test as client
        $this->actingAs($this->client);
        $clientComponent = Livewire::test('tickets.quick-actions', ['ticket' => $this->ticket]);
        
        // Client should be able to reply but not assign or merge
        $clientComponent->assertMethodWired('reply')
                       ->assertMethodNotWired('assignToMe') // This method should be gated
                       ->assertMethodNotWired('merge');
        
        // Test as admin
        $this->actingAs($this->admin);
        $adminComponent = Livewire::test('tickets.quick-actions', ['ticket' => $this->ticket]);
        
        // Admin should have access to all actions
        $adminComponent->assertMethodWired('reply')
                      ->assertMethodWired('assignToMe')
                      ->assertMethodWired('merge')
                      ->assertMethodWired('close');
    }

    /** @test */
    public function test_ticket_details_display_with_truncation()
    {
        // Create organization with long name
        $longOrgName = str_repeat('Very Long Organization Name ', 10);
        $this->ticket->organization->update(['name' => $longOrgName]);
        
        $this->actingAs($this->admin);
        $component = Livewire::test('tickets.view-ticket', ['ticket' => $this->ticket]);
        
        // Should see truncated organization name with title attribute
        $component->assertSee('title="' . $longOrgName . '"', false);
    }
}