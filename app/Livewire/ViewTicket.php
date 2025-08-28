<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Contracts\SettingsRepositoryInterface;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\TicketNote;
use App\Models\User;
use App\Models\TicketStatus as TicketStatusModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ViewTicket extends Component
{
    protected $listeners = ['refresh-notes' => 'refreshNotes', 'edit:toggle' => 'enableEdit'];
    private $lastDispatchTime = 0;
    
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
    
    // Form state management
    public string $activeInput = '';
    public string $replyMessage = '';
    public string $replyStatus = 'in_progress';
    public array $attachments = [];

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
            'organization.contracts' => function($query) {
                $query->select(['id', 'organization_id', 'department_id', 'contract_number', 'type', 'status', 'start_date', 'end_date', 'csi_number'])
                      ->orderBy('start_date', 'desc');
            },
            'department.departmentGroup:id,name',
            'owner:id,name'
        ])->loadCount(['notes', 'attachments']);

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
                'attachments:id,uuid,ticket_message_id,original_name,path,mime_type,size'
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
        // Cannot edit closed tickets
        if ($this->ticket->status === 'closed') {
            return false;
        }
        
        $user = auth()->user();
        return $user->hasRole('admin') || $user->can('tickets.update');
    }

    #[Computed]
    public function canAddNotes()
    {
        // Cannot add notes to closed tickets
        if ($this->ticket->status === 'closed') {
            return false;
        }
        
        return auth()->user()->hasRole('admin') || auth()->user()->hasRole('support');
    }

    #[Computed]
    public function canReply()
    {
        // Cannot reply to closed tickets
        if ($this->ticket->status === 'closed') {
            return false;
        }
        
        $user = auth()->user();

        // Clients can only reply to their own organization's tickets
        if ($user->hasRole('client')) {
            return $this->ticket->organization_id === $user->organization_id;
        }

        // Support can reply to tickets in their department or department group
        if ($user->hasRole('support')) {
            // Check same department first
            if ($this->ticket->department_id === $user->department_id) {
                return true;
            }
            // Check same department group
            if ($user->department?->department_group_id && 
                $user->department->department_group_id === $this->ticket->department?->department_group_id) {
                return true;
            }
            return false;
        }

        // Admins can reply to any ticket
        return $user->hasRole('admin');
    }

    #[Computed]
    public function activeContract()
    {
        return $this->ticket->organization->contracts->first();
    }

    #[Computed]
    public function priorityHexColor()
    {
        $colorService = app(\App\Services\TicketColorService::class);
        $priorityColors = $colorService->getPriorityColors();
        return $priorityColors[$this->ticket->priority] ?? '#3b82f6';
    }

    #[Computed]
    public function statusHexColor()
    {
        $statusModel = \App\Models\TicketStatus::where('key', $this->ticket->status)->first();
        return $statusModel ? $statusModel->color : '#3b82f6';
    }

    #[Computed]
    public function canReopen()
    {
        if ($this->ticket->status !== 'closed') {
            return false;
        }

        $user = auth()->user();
        
        // Admin and support can always reopen
        if ($user->hasRole(['admin', 'support'])) {
            return true;
        }

        // Clients can reopen within window
        if ($user->hasRole('client') && $user->organization_id === $this->ticket->organization_id) {
            $reopenLimit = app(\App\Contracts\SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3);
            return $this->ticket->closed_at && now()->diffInDays($this->ticket->closed_at) <= $reopenLimit;
        }

        return false;
    }

    public function enableEdit()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'You do not have permission to edit this ticket.');
            return;
        }

        $this->editMode = true;
    }



    public function openAttachmentPreview($attachmentId): void
    {
        $currentTime = microtime(true);
        
        // Prevent rapid successive dispatches (within 100ms)
        if ($currentTime - $this->lastDispatchTime < 0.1) {
            return;
        }
        
        $this->lastDispatchTime = $currentTime;

        $this->dispatch('open-attachment-preview', $attachmentId);
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
        logger()->info('updateTicket method called', [
            'user_id' => auth()->id(),
            'ticket_id' => $this->ticket->id,
            'form_data' => $this->form
        ]);
        
        try {
            if (! $this->canEdit) {
                session()->flash('error', 'You do not have permission to edit this ticket.');
                return;
            }

            $user = auth()->user();

            $this->validate([
                'form.status' => TicketStatusModel::validationRule(),
                'form.priority' => TicketPriority::validationRule(),
                'form.owner_id' => 'nullable|exists:users,id',
                'form.department_id' => 'required|exists:departments,id',
            ]);

            // Check if status is allowed for user's department group
            if ($user->hasRole('support') && $user->department?->department_group_id) {
                $allowedStatuses = TicketStatusModel::optionsForDepartmentGroup($user->department->department_group_id);
                if (!array_key_exists($this->form['status'], $allowedStatuses)) {
                    session()->flash('error', 'This ticket status is not available for your department group.');
                    return;
                }
            }

            // Check priority escalation for clients
            $previousPriority = $this->ticket->priority;
            if ($user->hasRole('client') && TicketPriority::compare($this->form['priority'], $previousPriority) > 0) {
                session()->flash('error', 'Clients cannot escalate ticket priority.');
                return;
            }

            // Check if this is a ticket reopening (from closed to any other status)
            $wasTicketClosed = $this->ticket->status === 'closed';
            $reopenLimit = app(SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3);
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
            
            session()->flash('error', 'Failed to update ticket. Please try again.');
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

            // Cannot assign closed tickets
            if ($this->ticket->status === 'closed') {
                session()->flash('error', 'Cannot assign a closed ticket.');
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
            $reopenLimit = app(SettingsRepositoryInterface::class)->get('tickets.reopen_window_days', 3);
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
            // Dispatch event to the TicketConversation component to open the edit modal
            $this->dispatch('edit-note', ['noteId' => $noteId, 'note' => $note->note, 'color' => $note->color, 'isInternal' => $note->is_internal])->to('tickets.ticket-conversation');
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

    public function refreshNotes()
    {
        $this->ticket->refresh()->load(['notes' => function($query) {
            $query->select(['id', 'ticket_id', 'user_id', 'note', 'color', 'is_internal', 'created_at'])
                  ->with('user:id,name')
                  ->latest();
        }]);
    }

    public function openReplyModal()
    {
        $this->dispatch('open-reply-modal')->to('tickets.ticket-conversation');
    }

    public function openNoteModal()
    {
        $this->dispatch('open-note-modal')->to('tickets.ticket-conversation');
    }

    public function sendMessage()
    {
        $this->validate([
            'replyMessage' => 'required|string|max:5000',
            'replyStatus' => 'required|in:open,in_progress,solution_provided',
        ]);

        try {
            // Create the message
            TicketMessage::create([
                'ticket_id' => $this->ticket->id,
                'sender_id' => auth()->id(),
                'message' => $this->replyMessage,
                'is_internal' => false,
                'is_system_message' => false,
            ]);

            // Update ticket status
            $this->ticket->update(['status' => $this->replyStatus]);

            // Reset form
            $this->replyMessage = '';
            $this->replyStatus = 'in_progress';
            $this->activeInput = '';
            $this->attachments = [];

            // Refresh ticket data
            $this->ticket->refresh();
            $this->loadConversation();

            session()->flash('message', 'Reply sent successfully.');
            $this->dispatch('message-sent');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send reply.');
        }
    }

    public function addNote()
    {
        $this->validate([
            'note' => 'required|string|max:2000',
            'noteColor' => 'required|in:sky,green,yellow,red,purple',
        ]);

        try {
            TicketNote::create([
                'ticket_id' => $this->ticket->id,
                'user_id' => auth()->id(),
                'note' => $this->note,
                'color' => $this->noteColor,
                'is_internal' => $this->noteInternal,
            ]);

            // Reset form
            $this->note = '';
            $this->noteColor = 'sky';
            $this->noteInternal = true;
            $this->activeInput = '';

            // Refresh notes
            $this->refreshNotes();

            session()->flash('message', 'Note added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add note.');
        }
    }

    public function updateNote()
    {
        $this->validate([
            'note' => 'required|string|max:2000',
            'noteColor' => 'required|in:sky,green,yellow,red,purple',
        ]);

        try {
            $note = TicketNote::find($this->editingNoteId);
            if ($note && ($note->user_id === auth()->id() || auth()->user()->hasRole('admin'))) {
                $note->update([
                    'note' => $this->note,
                    'color' => $this->noteColor,
                    'is_internal' => $this->noteInternal,
                ]);

                // Reset form
                $this->note = '';
                $this->noteColor = 'sky';
                $this->noteInternal = true;
                $this->editingNoteId = null;
                $this->activeInput = '';

                // Refresh notes
                $this->refreshNotes();

                session()->flash('message', 'Note updated successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to update note.');
        }
    }

    public function removeAttachment($index)
    {
        if (isset($this->attachments[$index])) {
            unset($this->attachments[$index]);
            $this->attachments = array_values($this->attachments); // Re-index array
        }
    }

    public function updatedFormPriority()
    {
        try {
            if (!$this->canEdit) {
                session()->flash('error', 'You do not have permission to edit this ticket.');
                return;
            }

            $user = auth()->user();
            $previousPriority = $this->ticket->priority;

            // Check priority escalation for clients
            if ($user->hasRole('client') && TicketPriority::compare($this->form['priority'], $previousPriority) > 0) {
                session()->flash('error', 'Clients cannot escalate ticket priority.');
                $this->form['priority'] = $previousPriority; // Reset to original
                return;
            }


            // Update the ticket priority
            $this->ticket->update(['priority' => $this->form['priority']]);

            // Log priority escalation if it's an increase
            if (TicketPriority::compare($this->form['priority'], $previousPriority) > 0) {
                ActivityLog::record('ticket.priority_escalated', $this->ticket->id, $this->ticket, [
                    'description' => "Priority escalated from {$previousPriority} to {$this->form['priority']}",
                    'old_priority' => $previousPriority,
                    'new_priority' => $this->form['priority']
                ]);
            }

            $this->ticket->refresh();
            session()->flash('message', 'Ticket priority updated successfully.');

        } catch (\Exception $e) {
            logger()->error('Failed to update ticket priority', [
                'user_id' => auth()->id(),
                'ticket_id' => $this->ticket->id,
                'new_priority' => $this->form['priority'],
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update ticket priority.');
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

        // Get status options based on user's department group
        $statusOptions = [];
        if ($user->hasRole('admin')) {
            $statusOptions = TicketStatusModel::options();
        } elseif ($user->hasRole('support') && $user->department?->department_group_id) {
            $statusOptions = TicketStatusModel::optionsForDepartmentGroup($user->department->department_group_id);
        } else {
            $statusOptions = TicketStatusModel::options(); // Fallback to default options
        }

        return view('livewire.tickets.view-ticket', [
            'departments' => $departments,
            'users' => $users,
            'statusOptions' => $statusOptions,
            'priorityOptions' => TicketPriority::options(),
        ]);
    }
}