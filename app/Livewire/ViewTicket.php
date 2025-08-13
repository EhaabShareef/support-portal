<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewTicket extends Component
{
    public Ticket $ticket;
    public bool $editMode = false;
    public bool $showCloseModal = false;

    public array $form = [
        'subject' => '',
        'status' => '',
        'priority' => '',
        'owner_id' => '',
        'department_id' => '',
        'description' => '',
    ];

    public array $closeForm = [
        'remarks' => '',
        'solution' => '',
    ];

    public ?int $confirmingNoteId = null;
    public string $note = '';
    public string $noteColor = 'sky';
    public bool $noteInternal = true;
    public ?int $editingNoteId = null;

    public function mount(Ticket $ticket)
    {
        // Check permissions before allowing access to ticket viewing
        $user = auth()->user();
        if (!$user || !$user->can('tickets.read')) {
            abort(403, 'Insufficient permissions to view tickets.');
        }
        
        // Additional authorization check - ensure user can access this specific ticket
        if (!$this->canAccessTicket($user, $ticket)) {
            abort(403, 'You do not have access to this ticket.');
        }

        $this->ticket = $ticket->load([
            'organization:id,name,notes',
            'organization.contracts' => function($query) use ($ticket) {
                $query->select(['id', 'organization_id', 'department_id', 'contract_number', 'type', 'status', 'start_date', 'end_date', 'contract_value', 'currency'])
                      ->where('department_id', $ticket->department_id)
                      ->where('status', 'active')
                      ->orderBy('start_date', 'desc')
                      ->limit(1);
            },
            'department:id,name,department_group_id',
            'department.departmentGroup:id,name',
            'owner:id,name',
            'client:id,name,email,organization_id',
            'notes' => function($query) {
                $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                      ->with('user:id,name')
                      ->latest();
            },
            'attachments:id,uuid,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image',
        ]);

        // Load conversation for closed tickets (read-only)
        if ($ticket->status === 'closed') {
            $this->loadConversation();
        }

        $this->form = [
            'subject' => $ticket->subject,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'owner_id' => $ticket->owner_id,
            'department_id' => $ticket->department_id,
            'description' => $ticket->description,
        ];
    }

    private function loadConversation(): void
    {
        // Create unified conversation combining messages and public notes
        $messages = $this->ticket->messages()
            ->select(['id', 'ticket_id', 'sender_id', 'message', 'is_system_message', 'created_at'])
            ->with([
                'sender:id,name',
                'attachments:id,uuid,attachable_id,attachable_type,original_name,stored_name,mime_type,size,is_image'
            ])
            ->selectRaw("'message' as type")
            ->get();
            
        $publicNotes = $this->ticket->notes()
            ->where('is_internal', false)
            ->select(['id', 'user_id as sender_id', 'note as message', 'created_at'])
            ->with('user:id,name')
            ->selectRaw("'note' as type")
            ->selectRaw("null as ticket_id")
            ->selectRaw("false as is_system_message")
            ->get()
            ->map(function ($note) {
                $note->attachments = collect(); // Add empty attachments collection
                return $note;
            });
            
        // Combine and sort by created_at descending
        $conversation = $messages->concat($publicNotes)->sortByDesc('created_at')->values();
        
        $this->ticket->setRelation('conversation', $conversation);
    }

    private function canAccessTicket($user, $ticket): bool
    {
        // Admin can see all tickets
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Client can only see their organization's tickets
        if ($user->hasRole('client')) {
            return $ticket->organization_id === $user->organization_id;
        }
        
        // Support staff can see tickets in their department group
        if ($user->hasRole('support') && $user->department) {
            if ($user->department->department_group_id) {
                return $ticket->department->department_group_id === $user->department->department_group_id;
            }
            return $ticket->department_id === $user->department_id;
        }
        
        return false;
    }

    #[Computed]
    public function canEdit()
    {
        $user = auth()->user();
        return $user->hasRole('admin') || $user->can('tickets.update');
    }

    #[Computed]
    public function canAddNotes()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('support');
    }

    #[Computed]
    public function activeContract()
    {
        return $this->ticket->organization->contracts->first();
    }

    public function enableEdit()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this ticket.');
            return;
        }

        $this->editMode = true;
    }

    public function cancelEdit()
    {
        $this->editMode = false;
        $this->form = [
            'subject' => $this->ticket->subject,
            'status' => $this->ticket->status,
            'priority' => $this->ticket->priority,
            'owner_id' => $this->ticket->owner_id,
            'department_id' => $this->ticket->department_id,
            'description' => $this->ticket->description,
        ];
    }

    public function updateTicket()
    {
        try {
            if (! $this->canEdit) {
                session()->flash('error', 'You do not have permission to edit this ticket.');
                return;
            }

            $this->validate([
                'form.status' => TicketStatus::validationRule(),
                'form.priority' => TicketPriority::validationRule(),
                'form.owner_id' => 'nullable|exists:users,id',
                'form.department_id' => 'required|exists:departments,id',
            ]);

            // Check priority escalation for clients
            $user = auth()->user();
            $previousPriority = $this->ticket->priority;
            if ($user->hasRole('client') && TicketPriority::compare($this->form['priority'], $previousPriority) > 0) {
                session()->flash('error', 'Clients cannot escalate ticket priority.');
                return;
            }

            // Check escalation authorization
            if (!$user->can('escalatePriority', [$this->ticket, $this->form['priority']])) {
                session()->flash('error', 'You are not authorized to change this ticket priority.');
                return;
            }

            // Check if this is a ticket reopening (from closed to any other status)
            $wasTicketClosed = $this->ticket->status === 'closed';
            $reopenLimit = Setting::get('tickets.reopen_window_days', 3);
            $isWithinWindow = $this->ticket->closed_at && now()->diffInDays($this->ticket->closed_at) <= $reopenLimit;
            $isTicketBeingReopened = $wasTicketClosed && $this->form['status'] !== 'closed' && $isWithinWindow;
            
            // Check reopen authorization for clients
            if ($wasTicketClosed && !$isWithinWindow && $user->hasRole('client')) {
                session()->flash('error', 'Ticket closed more than ' . $reopenLimit . ' days ago. Please create a new ticket.');
                return redirect()->route('tickets.create', ['subject' => 'Re: ' . $this->ticket->ticket_number]);
            }
            
            $previousStatus = $this->ticket->status;

            // Remove subject and description from update to prevent modification
            $updateData = $this->form;
            unset($updateData['subject'], $updateData['description']);
            
            $this->ticket->update($updateData);

            // Log priority escalation if it's an increase
            if (TicketPriority::compare($this->form['priority'], $previousPriority) > 0) {
                ActivityLog::record('ticket.priority_escalated', $this->ticket->id, $this->ticket, [
                    'description' => "Priority escalated from {$previousPriority} to {$this->form['priority']}",
                    'old_priority' => $previousPriority,
                    'new_priority' => $this->form['priority']
                ]);
            }
            
            // Create system message for status changes
            if ($previousStatus !== $this->form['status']) {
                $user = auth()->user();
                $status = $this->form['status'];
                
                $statusMessage = match($status) {
                    'closed' => "Ticket closed by {$user->name} on " . now()->format('M d, Y \\a\\t H:i'),
                    'solution_provided' => "Solution provided by {$user->name} on " . now()->format('M d, Y \\a\\t H:i'),
                    default => $isTicketBeingReopened 
                        ? "Ticket reopened by {$user->name} on " . now()->format('M d, Y \\a\\t H:i')
                        : "Ticket status changed to '{$status}' by {$user->name} on " . now()->format('M d, Y \\a\\t H:i')
                };

                TicketMessage::create([
                    'ticket_id' => $this->ticket->id,
                    'sender_id' => $user->id,
                    'message' => $statusMessage,
                    'is_internal' => false,
                    'is_system_message' => true,
                ]);
            }
            
            $this->ticket->refresh();
            $this->editMode = false;

            session()->flash('message', 'Ticket updated successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to update ticket', [
                'user_id' => auth()->id(),
                'ticket_id' => $this->ticket->id,
                'form_data' => $this->form,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to update ticket. Please try again or contact support if the problem persists.');
        }
    }

    public function assignToMe()
    {
        try {
            $user = auth()->user();

            if (!$user->can('tickets.assign')) {
                session()->flash('error', 'You do not have permission to assign tickets.');
                return;
            }

            if (!$this->canAccessTicket($user, $this->ticket)) {
                session()->flash('error', 'You cannot assign this ticket.');
                return;
            }

            $this->ticket->update(['owner_id' => $user->id]);
            $this->ticket->refresh();
            session()->flash('message', 'Ticket assigned to you successfully.');
        } catch (\Exception $e) {
            logger()->error('Failed to assign ticket', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to assign ticket.');
        }
    }

    public function changeStatus($status)
    {
        try {
            $user = auth()->user();

            if (!$user->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to update tickets.');
                return;
            }

            if (!$this->canAccessTicket($user, $this->ticket)) {
                session()->flash('error', 'You cannot update this ticket.');
                return;
            }

            // Check if this is a ticket reopening (from closed to any other status)
            $wasTicketClosed = $this->ticket->status === 'closed';
            $reopenLimit = Setting::get('tickets.reopen_window_days', 3);
            $isWithinWindow = $this->ticket->closed_at && now()->diffInDays($this->ticket->closed_at) <= $reopenLimit;
            $isTicketBeingReopened = $wasTicketClosed && $status !== 'closed' && $isWithinWindow;
            
            // Check reopen authorization for clients
            if ($wasTicketClosed && !$isWithinWindow && $user->hasRole('client')) {
                session()->flash('error', 'Ticket closed more than ' . $reopenLimit . ' days ago. Please create a new ticket.');
                return redirect()->route('tickets.create', ['subject' => 'Re: ' . $this->ticket->ticket_number]);
            }

            $updateData = ['status' => $status];

            if ($status === 'closed') {
                $updateData['closed_at'] = now();
            } elseif ($status === 'solution_provided') {
                $updateData['resolved_at'] = now();
            }

            $previousStatus = $this->ticket->status;
            $this->ticket->update($updateData);

            // Create system message for status changes
            if ($previousStatus !== $status) {
                $statusMessage = match($status) {
                    'closed' => "Ticket closed by {$user->name} on " . now()->format('M d, Y \\a\\t H:i'),
                    'solution_provided' => "Solution provided by {$user->name} on " . now()->format('M d, Y \\a\\t H:i'),
                    default => $isTicketBeingReopened 
                        ? "Ticket reopened by {$user->name} on " . now()->format('M d, Y \\a\\t H:i')
                        : "Ticket status changed to '{$status}' by {$user->name} on " . now()->format('M d, Y \\a\\t H:i')
                };

                TicketMessage::create([
                    'ticket_id' => $this->ticket->id,
                    'sender_id' => $user->id,
                    'message' => $statusMessage,
                    'is_internal' => false,
                    'is_system_message' => true,
                ]);
            }

            $this->ticket->refresh();
            session()->flash('message', 'Ticket status updated successfully.');
        } catch (\Exception $e) {
            logger()->error('Failed to update ticket status', [
                'ticket_id' => $this->ticket->id,
                'status' => $status,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update ticket status.');
        }
    }

    public function openCloseModal()
    {
        $this->closeForm = [
            'remarks' => '',
            'solution' => '',
        ];
        $this->showCloseModal = true;
    }

    public function submitClose()
    {
        try {
            if (! auth()->user()->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to close tickets.');
                return;
            }

            $this->validate([
                'closeForm.remarks' => 'nullable|string|max:2000',
                'closeForm.solution' => 'nullable|string|max:500',
            ]);

            // Always create a system-only closing message
            TicketMessage::create([
                'ticket_id' => $this->ticket->id,
                'sender_id' => Auth::id(),
                'message' => "Ticket closed by " . auth()->user()->name . " on " . now()->format('M d, Y \\a\\t H:i'),
                'is_internal' => false,
                'is_system_message' => true,
            ]);

            // Create client-visible message only if remarks are provided
            if (!empty($this->closeForm['remarks'])) {
                TicketMessage::create([
                    'ticket_id' => $this->ticket->id,
                    'sender_id' => Auth::id(),
                    'message' => $this->closeForm['remarks'],
                    'is_internal' => false,
                    'is_system_message' => false,
                ]);
            }

            // Create internal solution summary if provided
            if (!empty($this->closeForm['solution'])) {
                TicketNote::create([
                    'ticket_id' => $this->ticket->id,
                    'user_id' => Auth::id(),
                    'note' => "Solution Summary: " . $this->closeForm['solution'],
                    'color' => 'green',
                    'is_internal' => true,
                ]);
            }

            $this->ticket->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            $this->ticket = $this->ticket->fresh();
            $this->loadConversation(); // Reload conversation for closed ticket display
            $this->showCloseModal = false;

            session()->flash('message', 'Ticket closed successfully.');
        } catch (\Exception $e) {
            logger()->error('Failed to close ticket', [
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to close ticket.');
        }
    }

    public function editNote($noteId)
    {
        $note = TicketNote::find($noteId);
        if ($note && ($note->user_id === auth()->id() || auth()->user()->hasRole('admin'))) {
            $this->editingNoteId = $noteId;
            $this->note = $note->note;
            $this->noteColor = $note->color;
            $this->noteInternal = $note->is_internal;
        }
    }

    public function cancelEditNote()
    {
        $this->editingNoteId = null;
        $this->note = '';
        $this->noteColor = 'sky';
        $this->noteInternal = true;
    }

    public function confirmDelete($noteId)
    {
        $this->confirmingNoteId = $noteId;
    }

    public function cancelDelete()
    {
        $this->confirmingNoteId = null;
    }

    public function deleteNote($noteId)
    {
        try {
            $note = TicketNote::find($noteId);
            if ($note && ($note->user_id === auth()->id() || auth()->user()->hasRole('admin'))) {
                $note->delete();
                $this->ticket->refresh();
                $this->confirmingNoteId = null;
                session()->flash('message', 'Note deleted successfully.');
            }
        } catch (\Exception $e) {
            logger()->error('Failed to delete note', [
                'note_id' => $noteId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to delete note.');
        }
    }

    public function render()
    {
        $user = auth()->user();

        // Filter departments and users based on role
        $departments = collect();
        $users = collect();

        if ($user->hasRole('admin')) {
            $departments = Department::orderBy('name')->get();
            $users = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['support', 'admin']);
            })->orderBy('name')->get();
        } elseif ($user->hasRole('support')) {
            if ($user->department?->department_group_id) {
                // Show all departments in the same group
                $departments = Department::where('department_group_id', $user->department->department_group_id)
                    ->orderBy('name')->get();
                $users = User::whereHas('department', function ($q) use ($user) {
                    $q->where('department_group_id', $user->department->department_group_id);
                })->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['support', 'admin']);
                })->orderBy('name')->get();
            } else {
                // Fallback to just their department
                $departments = Department::where('id', $user->department_id)->get();
                $users = User::where('department_id', $user->department_id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['support', 'admin']);
                    })->orderBy('name')->get();
            }
        }

        return view('tickets.show', [
            'departments' => $departments,
            'users' => $users,
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
        ]);
    }
}