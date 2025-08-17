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

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

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

        \App\Models\TicketCcRecipient::where('ticket_id', $this->ticket->id)->update(['active' => false]);

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

    public function render()
    {
        return view('livewire.tickets.close-modal');
    }
}
