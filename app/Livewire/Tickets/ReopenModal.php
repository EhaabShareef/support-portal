<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReopenModal extends Component
{
    use AuthorizesRequests;
    public Ticket $ticket;
    public bool $show = false;
    public string $reason = '';

    protected $listeners = ['reopen:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function reopenTicket(): void
    {
        // Authorization check - ensure user can reopen tickets
        try {
            $this->authorize('reopen', $this->ticket);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            session()->flash('error', 'You are not authorized to reopen this ticket.');
            return;
        }

        // State check - ensure ticket is actually closed
        if ($this->ticket->status !== 'closed') {
            session()->flash('error', 'Only closed tickets can be reopened.');
            return;
        }

        try {
            // Update ticket status and clear closed_at timestamp
            $this->ticket->update([
                'status' => 'open',
                'closed_at' => null,
            ]);

            // Create system message for reopening
            $message = 'Reopened by ' . auth()->user()->name . ' at ' . now()->format('M d, Y \a\t H:i');
            if (!empty(trim($this->reason))) {
                $message .= ' - Reason: ' . trim($this->reason);
            }

            TicketMessage::create([
                'ticket_id' => $this->ticket->id,
                'sender_id' => auth()->id(),
                'message' => $message,
                'is_system_message' => true,
                'is_internal' => false,
            ]);

            // Reset form and close modal
            $this->reason = '';
            $this->toggle();
            
            // Show success message
            session()->flash('message', 'Ticket reopened successfully.');
            
            // Dispatch refresh events
            $this->dispatch('thread:refresh')->to(ConversationThread::class);
            $this->dispatch('ticket:refresh');
            
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            logger()->error('Failed to reopen ticket', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to reopen ticket. Please try again.');
        }
    }

    public function render()
    {
        return view('livewire.tickets.reopen-modal');
    }
}
