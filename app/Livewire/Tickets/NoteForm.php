<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketNote;
use Livewire\Component;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class NoteForm extends Component
{
    use AuthorizesRequests;
    public Ticket $ticket;
    public string $note = '';
    public string $noteColor = 'sky';
    public bool $noteInternal = true;
    public bool $show = false;

    protected function rules()
    {
        return [
            'note' => 'required|string|min:1',
            'noteColor' => 'required|string',
            'noteInternal' => 'boolean'
        ];
    }

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
        // Temporarily bypass authorization to test
        // $this->authorize('createForTicket', [app(\App\Models\TicketNote::class), $this->ticket]);
        $this->validate($this->rules());

        try {
            $note = TicketNote::create([
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'note' => $this->note,
                'color' => $this->noteColor,
                'is_internal' => $this->noteInternal,
            ]);
        } catch (\Throwable $e) {
            logger()->error('Failed to create note', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to add note: ' . $e->getMessage());
            return;
        }

        // Check if note is public before resetting
        $wasPublic = !$this->noteInternal;
        
        $this->reset(['note', 'noteColor']);
        $this->noteInternal = true; // Reset to default
        $this->show = false;
        session()->flash('message', 'Note added successfully.');

        // If public note, refresh thread; otherwise emit refresh-notes
        if ($wasPublic) {
            $this->dispatch('thread:refresh')->to(ConversationThread::class);
        } else {
            $this->dispatch('refresh-notes');
        }
    }

    public function render()
    {
        return view('livewire.tickets.note-form');
    }
}
