<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ReplyForm extends Component
{
    use WithFileUploads;

    public Ticket $ticket;
    public string $replyMessage = '';
    public string $replyStatus = 'in_progress';
    public array $attachments = [];
    public bool $show = false;

    protected $rules = [
        'replyMessage' => 'required|string|max:2000',
        'replyStatus' => 'required|string',
        'attachments.*' => 'file|max:10240'
    ];

    protected $listeners = ['reply:toggle' => 'toggle'];

    /**
     * Initialize the component with the ticket that will be replied to.
     *
     * @param \App\Models\Ticket $ticket The ticket instance this reply form is associated with.
     * @return void
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * Toggle the visibility of the reply form.
     *
     * Flips the component's public $show flag between true and false.
     */
    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    /**
     * Validate the reply input, persist a new ticket message, update the ticket status, and refresh the thread.
     *
     * Validates the component's rules, creates a non-system TicketMessage for the current ticket using the
     * authenticated user as sender, updates the ticket's status to the selected reply status, clears the
     * reply message and attachments, and dispatches a `thread:refresh` event to the ConversationThread component.
     */
    public function sendMessage(): void
    {
        $this->validate();

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'message' => $this->replyMessage,
            'is_system_message' => false,
        ]);

        $this->ticket->update(['status' => $this->replyStatus]);

        $this->reset(['replyMessage','attachments']);

        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    /**
     * Render the Livewire view for the ticket reply form.
     *
     * Returns the view used to display the reply UI for a ticket.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.tickets.reply-form');
    }
}
