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

    public function render()
    {
        return view('livewire.tickets.reopen-modal');
    }
}
