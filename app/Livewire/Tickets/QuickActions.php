<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use App\Livewire\Tickets\SplitTicketModal;

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
        $this->authorize('reply', $this->ticket);
        $this->dispatch('reply:toggle');
    }

    public function note(): void
    {
        $this->authorize('addNote', $this->ticket);
        $this->dispatch('note:toggle');
    }

    public function edit(): void
    {
        $this->authorize('update', $this->ticket);
        $this->dispatch('edit:toggle');
    }

    public function close(): void
    {
        $this->authorize('update', $this->ticket);
        $this->dispatch('close:toggle');
    }

    public function reopen(): void
    {
        $this->authorize('update', $this->ticket);
        $this->dispatch('reopen:toggle');
    }

    public function assignToMe(): void
    {
        $this->authorize('assign', $this->ticket);
        
        $this->ticket->update([
            'owner_id' => auth()->id()
        ]);

        session()->flash('success', 'Ticket assigned to you successfully.');
        $this->dispatch('ticket:refresh');
    }

    public function merge(): void
    {
        $this->authorize('update', $this->ticket);
        $this->dispatch('merge:toggle');
    }

    public function showSplit(): void
    {
        $this->dispatch('split:toggle')->to(SplitTicketModal::class);
    }

    public function render()
    {
        return view('livewire.tickets.quick-actions');
    }
}
