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
    
    // Merge options
    public bool $preservePriority = false;
    public bool $preserveStatus = false;
    public bool $combineSubjects = true;
    public bool $preserveOwner = false;
    public bool $showAdvancedOptions = false;
    
    protected $listeners = ['merge:toggle' => 'toggle'];

    protected $rules = [
        'ticketsInput' => 'required|string',
    ];

    public function mount($ticket = null): void
    {
        if ($ticket instanceof Ticket) {
            $this->ticket = $ticket;
            \Log::info('MergeTicketsModal::mount - Ticket set', [
                'ticketId' => $ticket->id,
                'ticketNumber' => $ticket->ticket_number
            ]);
        } else {
            $this->ticket = null;
            \Log::info('MergeTicketsModal::mount - No ticket provided');
        }
    }

    public function toggle($ticketId = null): void
    {
        if ($ticketId && !$this->ticket) {
            $this->ticket = Ticket::find($ticketId);
            \Log::info('MergeTicketsModal::toggle - Found ticket by ID', [
                'ticketId' => $ticketId,
                'ticket' => $this->ticket ? $this->ticket->toArray() : null
            ]);
        }
        
        $this->show = ! $this->show;
        if ($this->show) {
            $this->reset(['ticketsInput', 'foundTickets', 'validationErrors']);
            \Log::info('MergeTicketsModal::toggle - Modal opened', [
                'ticket' => $this->ticket ? $this->ticket->toArray() : null,
                'show' => $this->show
            ]);
        } else {
            $this->ticket = null;
            \Log::info('MergeTicketsModal::toggle - Modal closed, ticket reset to null');
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
            $foundTicket = Ticket::where('ticket_number', $ticketNumber)->first();

            if (!$foundTicket) {
                $this->validationErrors[] = "Ticket #{$ticketNumber} not found.";
                continue;
            }

            // Check if ticket is closed
            if ($foundTicket->status === 'closed') {
                $this->validationErrors[] = "Ticket #{$ticketNumber} is closed and cannot be merged.";
                continue;
            }

            // Check if it's the same ticket
            if ($foundTicket->id === $this->ticket->id) {
                $this->validationErrors[] = "Cannot merge ticket with itself (#{$ticketNumber}).";
                continue;
            }

            // Check organization consistency
            if ($foundTicket->organization_id !== $this->ticket->organization_id) {
                $this->validationErrors[] = "Ticket #{$ticketNumber} belongs to a different organization and cannot be merged.";
                continue;
            }

            // Check authorization
            try {
                $this->authorize('update', $foundTicket);
            } catch (\Exception $e) {
                $this->validationErrors[] = "You don't have permission to merge ticket #{$ticketNumber}.";
                continue;
            }

            // Add to found tickets with more details
            $this->foundTickets[] = [
                'id' => $foundTicket->id,
                'ticket_number' => $foundTicket->ticket_number,
                'subject' => $foundTicket->subject,
                'status' => $foundTicket->status,
                'priority' => $foundTicket->priority,
                'owner_name' => $foundTicket->owner?->name ?? 'Unassigned',
                'created_at' => $foundTicket->created_at->format('M j, Y'),
                'is_merged' => $foundTicket->is_merged,
                'merged_into' => $foundTicket->merged_into?->ticket_number ?? null,
            ];
        }
    }

    public function merge(TicketMergeService $service)
    {
        logger()->info('=== MergeTicketsModal::merge called ===', [
            'ticketsInput' => $this->ticketsInput,
            'foundTickets' => $this->foundTickets,
            'validationErrors' => $this->validationErrors,
            'mergeOptions' => $this->getMergeOptions()
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
            'foundTicketIds' => array_column($this->foundTickets, 'id'),
            'mergeOptions' => $this->getMergeOptions()
        ]);

        try {
            // Perform the merge operation with options
            $mergedTicket = $service->merge($ticketIds, auth()->id(), $this->getMergeOptions());
            
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

    private function getMergeOptions(): array
    {
        return [
            'preserve_priority' => $this->preservePriority,
            'preserve_status' => $this->preserveStatus,
            'combine_subjects' => $this->combineSubjects,
            'preserve_owner' => $this->preserveOwner,
        ];
    }

    public function render()
    {
        return view('livewire.tickets.merge-tickets-modal');
    }
}
