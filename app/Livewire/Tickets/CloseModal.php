<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;

class CloseModal extends Component
{
    public Ticket $ticket;
    public bool $show = false;
    public string $remarks = '';
    public string $solution = '';

    protected $listeners = ['close:toggle' => 'toggle'];

    /**
     * Initialize the component with the given ticket.
     *
     * Assigns the provided Ticket model instance to the component's `$ticket` property
     * so the modal operates on that ticket.
     *
     * @param \App\Models\Ticket $ticket The ticket to be managed by this component.
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * Toggle the component's visibility flag.
     *
     * Flips the public boolean $show used to control whether the close modal is visible.
     */
    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    /**
     * Close the current ticket, record closure messages, and refresh the thread view.
     *
     * Updates the ticket's status to "closed", creates a system TicketMessage containing
     * either the provided remarks or a default "Closed by {user} at {time}" statement,
     * and — if a solution string is provided — creates an additional non-system
     * TicketMessage with that solution. After processing, toggles this component's
     * visibility and dispatches a `thread:refresh` event to ConversationThread.
     */
    public function closeTicket(): void
    {
        $this->ticket->update(['status' => 'closed']);

        $message = $this->remarks ?: 'Closed by '.auth()->user()->name.' at '.now();

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'message' => $message,
            'is_system_message' => true,
        ]);

        if ($this->solution) {
            TicketMessage::create([
                'ticket_id' => $this->ticket->id,
                'sender_id' => auth()->id(),
                'message' => $this->solution,
                'is_system_message' => false,
            ]);
        }

        $this->toggle();
        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    /**
     * Render the Livewire close modal view.
     *
     * Returns the view used to display the ticket close modal.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.tickets.close-modal');
    }
}
