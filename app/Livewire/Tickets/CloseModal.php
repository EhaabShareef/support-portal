<?php

namespace App\Livewire\Tickets;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CloseModal extends Component
{
    public Ticket $ticket;
    public bool $show = false;
    public string $remarks = '';
    public string $solution = '';

    protected $listeners = ['close:toggle' => 'toggle'];

    public function mount(Ticket $ticket): void
    {
        // Authorization check - ensure user can close tickets
        if (!Gate::allows('update', $ticket)) {
            abort(403, 'You are not authorized to close this ticket.');
        }
        
        $this->ticket = $ticket;
    }

    public function toggle(): void
    {
        $this->show = ! $this->show;
    }

    public function closeTicket(): void
    {
        // Authorization check - ensure user can still close this ticket
        if (!Gate::allows('update', $this->ticket)) {
            $this->addError('general', 'You are not authorized to close this ticket.');
            return;
        }

        try {
            // Wrap in database transaction for atomicity
            DB::transaction(function () {
                // Update ticket status and set closed_at timestamp
                $this->ticket->update([
                    'status' => 'closed',
                    'closed_at' => now(),
                ]);

                // Use a single timestamp and actor for all records below
                $now = now();
                $actorId = auth()->id();
                $actorName = auth()->user()?->name ?? 'System';

                // Create system message for closure (machine-generated)
                $systemMessage = 'Closed by ' . $actorName . ' at ' . $now->format('M d, Y \a\t H:i');
                TicketMessage::create([
                    'ticket_id'         => $this->ticket->id,
                    'sender_id'         => $actorId,
                    'message'           => $systemMessage,
                    'is_system_message' => true,
                    'is_internal'       => false,
                ]);
                // Create remarks message if provided (human-authored, non-system)
                $remarks = trim((string) $this->remarks);
                if ($remarks !== '') {
                    TicketMessage::create([
                        'ticket_id'         => $this->ticket->id,
                        'sender_id'         => $actorId,
                        'message'           => $remarks,
                        'is_system_message' => false,
                        'is_internal'       => false,
                    ]);
                }

                // Create solution message if provided (human-authored, non-system)
                $solution = trim((string) $this->solution);
                if ($solution !== '') {
                    TicketMessage::create([
                        'ticket_id'         => $this->ticket->id,
                        'sender_id'         => $actorId,
                        'message'           => $solution,
                        'is_system_message' => false,
                        'is_internal'       => false,
                    ]);
                }
            });

            // Reset form and close modal
            $this->remarks = '';
            $this->solution = '';
            $this->toggle();
            
            // Dispatch refresh event
            $this->dispatch('thread:refresh')->to(ConversationThread::class);
            
            // Show success message
            session()->flash('message', 'Ticket closed successfully.');
            
        } catch (\Exception $e) {
            // Log error and show user-friendly message
            logger()->error('Failed to close ticket', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addError('general', 'Failed to close ticket. Please try again or contact support if the problem persists.');
        }
    }

    public function render()
    {
        return view('livewire.tickets.close-modal');
    }
}
