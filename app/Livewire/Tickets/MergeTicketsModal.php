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
    
    public ?Ticket $ticket = null;
    public string $ticketsInput = '';
    public bool $show = false;
    public array $foundTickets = [];
    public array $validationErrors = [];
    
    protected $listeners = ['merge:toggle' => 'toggle'];

    protected $rules = [
        'ticketsInput' => 'required|string',
    ];

    public function mount($ticket = null): void
    {
        if ($ticket instanceof Ticket) {
            $this->ticket = $ticket;
        } else {
            $this->ticket = null;
        }
    }

    public function toggle($ticketId = null): void
    {
        if ($ticketId && !$this->ticket) {
            $this->ticket = Ticket::find($ticketId);
        }
        
        $this->show = ! $this->show;
        if ($this->show) {
            $this->reset(['ticketsInput', 'foundTickets', 'validationErrors']);
        } else {
            $this->ticket = null;
        }
    }

    public function updatedTicketsInput()
    {
        $this->validateTickets();
    }

    public function validateTickets()
    {
        $this->foundTickets = [];
        $this->validationErrors = [];

        if (!$this->ticket) {
            return;
        }

        if (empty(trim($this->ticketsInput))) {
            return;
        }

        // Parse ticket numbers (allow various separators)
        $ticketNumbers = preg_split('/[,;\s]+/', trim($this->ticketsInput));
        $ticketNumbers = array_filter(array_map('trim', $ticketNumbers));

        foreach ($ticketNumbers as $ticketNumber) {
            if (empty($ticketNumber)) {
                continue;
            }

            // Find ticket by ticket_number
            $ticket = Ticket::where('ticket_number', $ticketNumber)->first();

            if (!$ticket) {
                $this->validationErrors[] = "Ticket #{$ticketNumber} not found.";
                continue;
            }

            // Check if ticket is closed
            if ($ticket->status === 'closed') {
                $this->validationErrors[] = "Ticket #{$ticketNumber} is closed and cannot be merged.";
                continue;
            }

            // Check if it's the same ticket
            if ($ticket->id === $this->ticket->id) {
                $this->validationErrors[] = "Cannot merge ticket with itself (#{$ticketNumber}).";
                continue;
            }

            // Check if ticket is already merged into another ticket
            if ($ticket->is_merged) {
                $this->validationErrors[] = "Ticket #{$ticketNumber} has already been merged into another ticket and cannot be merged again.";
                continue;
            }

            // Check authorization
            try {
                $this->authorize('update', $ticket);
            } catch (\Exception $e) {
                $this->validationErrors[] = "You don't have permission to merge ticket #{$ticketNumber}.";
                continue;
            }

            // Add to found tickets
            $this->foundTickets[] = [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'subject' => $ticket->subject,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'created_at' => $ticket->created_at->format('M j, Y'),
            ];
        }
    }

    public function merge(TicketMergeService $service)
    {
        logger()->info('=== MergeTicketsModal::merge called ===', [
            'ticketsInput' => $this->ticketsInput,
            'foundTickets' => $this->foundTickets,
            'validationErrors' => $this->validationErrors
        ]);

        if (!$this->ticket) {
            session()->flash('error', 'No ticket selected for merging.');
            return;
        }

        $this->validate();
        $this->validateTickets();

        logger()->info('=== After validation ===', [
            'validationErrors' => $this->validationErrors,
            'foundTickets' => $this->foundTickets
        ]);

        if (!empty($this->validationErrors)) {
            session()->flash('error', 'Please fix the validation errors before merging.');
            return;
        }

        if (empty($this->foundTickets)) {
            session()->flash('error', 'No valid tickets found to merge.');
            return;
        }

        // Collect all ticket IDs (current ticket + found tickets)
        $ticketIds = array_merge(
            [$this->ticket->id],
            array_column($this->foundTickets, 'id')
        );

        logger()->info('=== About to merge tickets ===', [
            'ticketIds' => $ticketIds,
            'currentTicketId' => $this->ticket->id,
            'foundTicketIds' => array_column($this->foundTickets, 'id')
        ]);

        try {
            // Perform the merge operation
            $mergedTicket = $service->merge($ticketIds, auth()->id());
            
            logger()->info('=== Merge successful ===', [
                'mergedTicketId' => $mergedTicket->id,
                'mergedTicketNumber' => $mergedTicket->ticket_number
            ]);
            
            // Success: redirect to the merged ticket
            session()->flash('success', 'Tickets merged successfully.');
            return redirect()->route('tickets.show', $mergedTicket);
            
        } catch (DomainException $e) {
            logger()->error('=== Merge failed with DomainException ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Handle domain-specific errors gracefully
            session()->flash('error', $e->getMessage());
            return;
        } catch (\Exception $e) {
            logger()->error('=== Merge failed with Exception ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'An unexpected error occurred during merge: ' . $e->getMessage());
            return;
        }
    }

    public function render()
    {
        return view('livewire.tickets.merge-tickets-modal');
    }
}
