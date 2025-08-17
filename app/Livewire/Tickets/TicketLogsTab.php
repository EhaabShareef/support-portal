<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use Livewire\Component;

class TicketLogsTab extends Component
{
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }
    }

    public function render()
    {
        $logs = $this->ticket->messages()->where('is_log', true)->latest()->get();
        return view('livewire.tickets.ticket-logs-tab', [
            'logs' => $logs,
        ]);
    }
}
