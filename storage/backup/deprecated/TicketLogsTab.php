<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\PermissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class TicketLogsTab extends Component
{
    use AuthorizesRequests;
    
    public Ticket $ticket;

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
        
        // Use centralized policy-based authorization
        $this->authorize('viewLogs', $this->ticket);
    }

    public function render()
    {
        $logs = $this->ticket->messages()->where('is_log', true)->latest()->get();
        return view('livewire.tickets.ticket-logs-tab', [
            'logs' => $logs,
        ]);
    }
}
