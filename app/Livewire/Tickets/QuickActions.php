<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class QuickActions extends Component
{
    use AuthorizesRequests;

    public Ticket $ticket;

    protected $listeners = ['ticket:refresh' => '$refresh'];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function reply(): void
    {
        // Cannot reply to closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'Cannot reply to a closed ticket.');
            return;
        }
        
        $this->authorize('reply', $this->ticket);
        $this->dispatch('reply:toggle')->to('tickets.reply-form');
    }

    public function note(): void
    {
        // Cannot add notes to closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'Cannot add notes to a closed ticket.');
            return;
        }
        
        $this->authorize('addNote', $this->ticket);
        $this->dispatch('note:toggle')->to('tickets.note-form');
    }

    public function edit(): void
    {
        // Cannot edit closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'Cannot edit a closed ticket.');
            return;
        }
        
        $this->authorize('update', $this->ticket);
        
        // If we're in the manage-tickets view, redirect to the ticket view for editing
        if (request()->routeIs('tickets.index')) {
            redirect()->route('tickets.show', $this->ticket);
        } else {
            // If we're already in the ticket view, dispatch the edit event
            $this->dispatch('edit:toggle')->to('view-ticket');
        }
    }

    public function close(): void
    {
        // Cannot close already closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'This ticket is already closed.');
            return;
        }
        
        $this->authorize('update', $this->ticket);
        $this->dispatch('close:toggle')->to('tickets.close-modal');
    }

    public function reopen(): void
    {
        $this->authorize('update', $this->ticket);
        $this->dispatch('reopen:toggle')->to('tickets.reopen-modal');
    }

    public function assignToMe(): void
    {
        // Cannot assign closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'Cannot assign a closed ticket.');
            return;
        }
        
        $this->authorize('assign', $this->ticket);
        
        $this->ticket->update([
            'owner_id' => auth()->id()
        ]);

        session()->flash('success', 'Ticket assigned to you successfully.');
        $this->dispatch('ticket:refresh');
    }

    public function merge(): void
    {
        // Cannot merge closed tickets
        if ($this->ticket->status === 'closed') {
            session()->flash('error', 'Cannot merge a closed ticket.');
            return;
        }
        
        $this->authorize('update', $this->ticket);
        $this->dispatch('merge:toggle')->to('tickets.merge-tickets-modal');
    }

    public function render()
    {
        return view('livewire.tickets.quick-actions');
    }
}
