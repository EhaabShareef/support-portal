<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketNote;
use Livewire\Component;

class NoteForm extends Component
{
    public Ticket $ticket;
    public string $note = '';
    public string $noteColor = 'sky';
    public bool $noteInternal = true;
    public bool $show = false;

    protected $rules = [
        'note' => 'required|string',
        'noteColor' => 'required|string',
        'noteInternal' => 'boolean'
    ];

    protected $listeners = ['note:toggle' => 'toggle'];

    /**
     * Initialize the component with the given ticket.
     *
     * Binds the provided Ticket instance to the component so subsequent actions
     * operate on this ticket.
     *
     * @param Ticket $ticket The ticket this component will add notes to.
     */
    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    /**
     * Toggle the form's visibility.
     *
     * Flips the component's `$show` flag between true and false to show or hide the note form.
     */
    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    /**
     * Validate the form and persist a new note for the bound ticket.
     *
     * Validates component state, creates a TicketNote linked to the current ticket and authenticated user
     * with the configured content, color, and internal flag, then resets the note inputs and signals
     * the ConversationThread component to refresh.
     *
     * @return void
     */
    public function addNote(): void
    {
        $this->validate();

        TicketNote::create([
            'ticket_id' => $this->ticket->id,
            'user_id' => auth()->id(),
            'note' => $this->note,
            'color' => $this->noteColor,
            'is_internal' => $this->noteInternal,
        ]);

        $this->reset(['note','noteColor','noteInternal']);

        $this->dispatch('thread:refresh')->to(ConversationThread::class);
    }

    /**
     * Render the ticket note Livewire component view.
     *
     * @return \Illuminate\View\View The rendered view for the ticket note form.
     */
    public function render()
    {
        return view('livewire.tickets.note-form');
    }
}
