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

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

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

    public function render()
    {
        return view('livewire.tickets.note-form');
    }
}
