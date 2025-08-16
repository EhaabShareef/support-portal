<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class QuickActions extends Component
{
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function showReply(): void
    {
        $this->dispatch('reply:toggle')->to(ReplyForm::class);
    }

    public function showNote(): void
    {
        $this->dispatch('note:toggle')->to(NoteForm::class);
    }

    public function showClose(): void
    {
        $this->dispatch('close:toggle')->to(CloseModal::class);
    }

    public function showReopen(): void
    {
        $this->dispatch('reopen:toggle')->to(ReopenModal::class);
    }

    public function render()
    {
        return view('livewire.tickets.quick-actions');
    }
}
