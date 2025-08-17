<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Services\Tickets\TicketMergeService;
use DomainException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class MergeTicketsModal extends Component
{
    use AuthorizesRequests;
    
    public Ticket $ticket;
    public string $ticketsInput = '';
    public bool $show = false;

    protected $rules = [
        'ticketsInput' => 'required|regex:/^\s*\d+(?:\s*,\s*\d+)*\s*$/',
    ];

    public function mount(Ticket $ticket): void
    {
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function merge(TicketMergeService $service)
    {
        $data = $this->validate();
        
        // Parse and normalize input IDs, remove duplicates, ensure current ticket ID is included
        $inputIds = array_filter(array_map('intval', explode(',', $data['ticketsInput'])));
        $ids = array_unique(array_merge([$this->ticket->id], $inputIds));
        
        // Authorization check - user must be able to update all tickets being merged
        foreach ($ids as $ticketId) {
            $ticketToCheck = Ticket::find($ticketId);
            if (!$ticketToCheck) {
                session()->flash('error', "Ticket #{$ticketId} not found.");
                return;
            }
            
            $this->authorize('update', $ticketToCheck);
        }
        
        try {
            // Perform the merge operation
            $mergedTicket = $service->merge($ids, auth()->id());
            
            // Success: redirect to the merged ticket
            session()->flash('success', 'Tickets merged successfully.');
            return redirect()->route('tickets.show', $mergedTicket);
            
        } catch (DomainException $e) {
            // Handle domain-specific errors gracefully
            session()->flash('error', $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.tickets.merge-tickets-modal');
    }
}
