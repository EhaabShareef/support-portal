<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;

class ReopenModal extends Component
{
    public Ticket $ticket;
    public bool $show = false;
    public string $reason = '';

    protected $listeners = ['reopen:toggle' => 'toggle'];

    /**
     * Initialize the component with the ticket to be reopened.
     *
     * @param Ticket $ticket The ticket instance this component will operate on.
     * @return void
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * Toggle the modal visibility state.
     *
     * Flips the component's `$show` flag between true and false to show or hide the reopen modal.
     */
    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    /**
     * Reopens the current ticket, logs a system message, closes the modal, and refreshes the conversation thread.
     *
     * The method sets the ticket's status to "open", creates a TicketMessage marked as a system message
     * containing the authenticated user's name and the current timestamp, and appends the optional
     * $reason if provided. After persisting the message it toggles the modal visibility and dispatches
     * a `thread:refresh` event targeted at the ConversationThread component.
     *
     * Note: This method assumes an authenticated user is available (uses `auth()`).
     */
    public function reopenTicket(): void
    {
        $this->ticket->update(['status' => 'open']);

        $message = 'Reopened by '.auth()->user()->name.' at '.now();
        if ($this->reason) {
            $message .= ' - '.$this->reason;
        }

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'message' => $message,
            'is_system_message' => true,
        ]);

        $this->toggle();
        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    /**
     * Render the Livewire view for the ticket reopen modal.
     *
     * @return \Illuminate\View\View The view for 'livewire.tickets.reopen-modal'.
     */
    public function render()
    {
        return view('livewire.tickets.reopen-modal');
    }
}
