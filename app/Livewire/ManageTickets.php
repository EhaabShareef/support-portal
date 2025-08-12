<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTickets extends Component
{
    use WithPagination;

    public function mount(): void
    {
        // Check permissions before allowing access to ticket management
        $user = auth()->user();
        if (!$user || !$user->can('tickets.read')) {
            abort(403, 'Insufficient permissions to view tickets.');
        }
    }

    public string $search = '';

    public string $filterStatus = '';

    public string $filterPriority = '';


    public string $filterOrg = '';

    public string $filterDept = '';

    public string $filterAssigned = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $quickFilter = 'all'; // 'all', 'my_tickets', 'my_department', 'my_department_group', 'unassigned'

    public bool $showClosed = false; // Toggle to show/hide closed and solution_provided tickets

    // Form properties for creating tickets
    public bool $showCreateModal = false;

    public array $form = [
        'subject' => '',
        'priority' => 'normal',
        'description' => '',
        'organization_id' => '',
        'client_id' => '',
        'department_id' => '',
    ];

    protected $rules = [
        'form.subject' => 'required|string|max:255',
        'form.priority' => 'required|in:low,normal,high,urgent,critical', // Will be updated dynamically  
        'form.description' => 'nullable|string',
        'form.organization_id' => 'required|exists:organizations,id',
        'form.client_id' => 'required|exists:users,id',
        'form.department_id' => 'required|exists:departments,id',
    ];

    public function rules()
    {
        return [
            'form.subject' => 'required|string|max:255',
            'form.priority' => TicketPriority::validationRule(),
            'form.description' => 'nullable|string',
            'form.organization_id' => 'required|exists:organizations,id',
            'form.client_id' => 'required|exists:users,id',
            'form.department_id' => 'required|exists:departments,id',
        ];
    }

    protected $messages = [
        'form.subject.required' => 'Please enter a subject for the ticket.',
        'form.subject.max' => 'Subject must not exceed 255 characters.',
        'form.priority.required' => 'Please select a priority level.',
        'form.organization_id.required' => 'Please select an organization.',
        'form.organization_id.exists' => 'The selected organization is invalid.',
        'form.client_id.required' => 'Please select a client for this ticket.',
        'form.client_id.exists' => 'The selected client is invalid.',
        'form.department_id.required' => 'Please select a department.',
        'form.department_id.exists' => 'The selected department is invalid.',
    ];

    public function updating($field)
    {
        if (in_array($field, ['search', 'filterStatus', 'filterPriority', 'filterOrg', 'filterDept', 'filterAssigned', 'quickFilter', 'showClosed'])) {
            $this->resetPage();
        }
    }

    public function setQuickFilter($filter)
    {
        $this->quickFilter = $filter;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function canCreate()
    {
        return auth()->user()->hasRole('admin') || auth()->user()->can('tickets.create');
    }


    public function openCreateModal()
    {
        $this->resetForm();

        // Auto-set organization for clients
        $user = auth()->user();
        if ($user->hasRole('client')) {
            $this->form['organization_id'] = $user->organization_id;
            $this->form['client_id'] = $user->id;
        }

        $this->showCreateModal = true;
    }


    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->form = [
            'subject' => '',
            'priority' => 'normal',
            'description' => '',
            'organization_id' => '',
            'client_id' => '',
            'department_id' => '',
        ];
        $this->resetErrorBag();
    }

    public function updatedFormOrganizationId($value)
    {
        // Reset client selection when organization changes
        $this->form['client_id'] = '';
    }

    #[Computed]
    public function availableClients()
    {
        if (! $this->form['organization_id']) {
            return collect();
        }

        return User::where('organization_id', $this->form['organization_id'])
            ->whereHas('roles', function ($q) {
                $q->where('name', 'client');
            })
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        try {
            $user = auth()->user();
            $this->validate();

            // Set defaults for new tickets
            $ticketData = $this->form;
            $ticketData['status'] = 'open';
            $ticketData['owner_id'] = null; // Keep unassigned

            // Enforce security constraints based on user role
            if ($user->hasRole('client')) {
                // Clients can only create tickets for their own organization
                $ticketData['organization_id'] = $user->organization_id;
                $ticketData['client_id'] = $user->id;
            } elseif ($user->hasRole('support')) {
                // Agents create tickets on behalf of clients - use selected client_id
                // Keep the client_id from form validation (already validated to exist)
                if (empty($ticketData['client_id'])) {
                    $this->addError('form.client_id', 'Please select a client for this ticket.');
                    return;
                }
            }

            Ticket::create($ticketData);
            session()->flash('message', 'Ticket created successfully.');

            $this->closeModal();
            
        } catch (\Exception $e) {
            logger()->error('Failed to create ticket via ManageTickets', [
                'user_id' => auth()->id(),
                'form_data' => $this->form,
                'error' => $e->getMessage()
            ]);
            
            session()->flash('error', 'Failed to create ticket. Please try again or contact support if the problem persists.');
        }
    }

    public function assignToMe($ticketId)
    {
        try {
            $user = auth()->user();
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user can assign tickets
            if (!$user->can('tickets.assign')) {
                session()->flash('error', 'You do not have permission to assign tickets.');
                return;
            }
            
            // Check if user can access this ticket based on role
            if (!$this->canAccessTicket($user, $ticket)) {
                session()->flash('error', 'You cannot assign this ticket.');
                return;
            }
            
            $ticket->update(['owner_id' => $user->id]);
            session()->flash('message', 'Ticket assigned to you successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to assign ticket', [
                'ticket_id' => $ticketId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to assign ticket.');
        }
    }

    public function closeTicket($ticketId)
    {
        try {
            $user = auth()->user();
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user can close tickets
            if (!$user->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to close tickets.');
                return;
            }
            
            // Check if user can access this ticket
            if (!$this->canAccessTicket($user, $ticket)) {
                session()->flash('error', 'You cannot close this ticket.');
                return;
            }
            
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now()
            ]);
            session()->flash('message', 'Ticket closed successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to close ticket', [
                'ticket_id' => $ticketId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to close ticket.');
        }
    }

    public function changePriority($ticketId, $priority)
    {
        try {
            $user = auth()->user();
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user can update tickets
            if (!$user->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to update tickets.');
                return;
            }
            
            // Check if user can access this ticket
            if (!$this->canAccessTicket($user, $ticket)) {
                session()->flash('error', 'You cannot update this ticket.');
                return;
            }

            // Check if client is trying to escalate priority
            if ($user->hasRole('client') && TicketPriority::compare($priority, $ticket->priority) > 0) {
                session()->flash('error', 'Clients cannot escalate ticket priority.');
                return;
            }

            // Check escalation authorization for all users
            if (!$user->can('escalatePriority', [$ticket, $priority])) {
                session()->flash('error', 'You are not authorized to change this ticket priority.');
                return;
            }

            $oldPriority = $ticket->priority;
            $ticket->update(['priority' => $priority]);

            // Log priority escalation if it's an increase
            if (TicketPriority::compare($priority, $oldPriority) > 0) {
                ActivityLog::record('ticket.priority_escalated', $ticket->id, $ticket, [
                    'description' => "Priority escalated from {$oldPriority} to {$priority}",
                    'old_priority' => $oldPriority,
                    'new_priority' => $priority
                ]);
            }

            session()->flash('message', 'Ticket priority updated successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to update ticket priority', [
                'ticket_id' => $ticketId,
                'priority' => $priority,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update ticket priority.');
        }
    }

    public function changeStatus($ticketId, $status)
    {
        try {
            $user = auth()->user();
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user can update tickets
            if (!$user->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to update tickets.');
                return;
            }
            
            // Check if user can access this ticket
            if (!$this->canAccessTicket($user, $ticket)) {
                session()->flash('error', 'You cannot update this ticket.');
                return;
            }
            
            // Check if this is a ticket reopening (from closed to any other status)
            $wasTicketClosed = $ticket->status === 'closed';
            $isTicketBeingReopened = $wasTicketClosed && $status !== 'closed';
            
            $updateData = ['status' => $status];
            
            // Set closed_at if closing the ticket
            if ($status === 'closed') {
                $updateData['closed_at'] = now();
            } elseif ($status === 'solution_provided') {
                $updateData['resolved_at'] = now();
            }
            
            $ticket->update($updateData);
            
            // If ticket is being reopened, create an automatic message
            if ($isTicketBeingReopened) {
                \App\Models\TicketMessage::create([
                    'ticket_id' => $ticket->id,
                    'sender_id' => $user->id,
                    'message' => "Ticket has been reopened by {$user->name} on " . now()->format('M d, Y \a\t H:i'),
                    'is_internal' => false,
                    'is_system_message' => true,
                ]);
            }
            
            session()->flash('message', 'Ticket status updated successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to update ticket status', [
                'ticket_id' => $ticketId,
                'status' => $status,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to update ticket status.');
        }
    }

    private function canAccessTicket($user, $ticket): bool
    {
        // Admin can access all tickets
        if ($user->hasRole('admin')) {
            return true;
        }
        
        // Client can only access their organization's tickets
        if ($user->hasRole('client')) {
            return $ticket->organization_id === $user->organization_id;
        }
        
        // Support staff can access tickets in their department group
        if ($user->hasRole('support') && $user->department) {
            if ($user->department->department_group_id) {
                return $ticket->department->department_group_id === $user->department->department_group_id;
            }
            return $ticket->department_id === $user->department_id;
        }
        
        return false;
    }


    public function render()
    {
        $user = auth()->user();
        $query = Ticket::query()
            ->with([
                'organization:id,name',
                'department:id,name,department_group_id',
                'department.departmentGroup:id,name',
                'client:id,name,email',
                'owner:id,name',
            ])
            ->withCount([
                'messages',
                'notes as internal_note_count' => fn($q) => $q->where('is_internal', true),
                'attachments'
            ]);

        // Apply quick filter first
        switch ($this->quickFilter) {
            case 'my_tickets':
                $query->where('owner_id', $user->id);
                break;
            case 'my_department':
                if ($user->department_id) {
                    $query->where('department_id', $user->department_id);
                }
                break;
            case 'my_department_group':
                if ($user->department_id && $user->department?->department_group_id) {
                    $query->forDepartmentGroup($user->department->department_group_id);
                }
                break;
            case 'unassigned':
                $query->whereNull('owner_id');
                if ($user->hasRole('support')) {
                    // Allow agents to see unassigned tickets from their department group
                    if ($user->department?->department_group_id) {
                        $query->forDepartmentGroup($user->department->department_group_id);
                    } else {
                        $query->where('department_id', $user->department_id);
                    }
                } elseif ($user->hasRole('client')) {
                    $query->where('organization_id', $user->organization_id);
                }
                break;
            case 'all':
            default:
                // Apply role-based filtering for 'all' tab
                if ($user->hasRole('support')) {
                    // Agents can see tickets in their department group
                    if ($user->department?->department_group_id) {
                        $query->forDepartmentGroup($user->department->department_group_id);
                    } else {
                        $query->where('department_id', $user->department_id);
                    }
                } elseif ($user->hasRole('client')) {
                    // Clients can only see tickets from their organization
                    $query->where('organization_id', $user->organization_id);
                }
                // Admin can see all tickets (no additional filtering)
                break;
        }

        // By default, hide closed and solution_provided tickets unless showClosed is true
        if (!$this->showClosed) {
            $query->whereNotIn('status', ['closed', 'solution_provided']);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('subject', 'like', "%{$this->search}%")
                    ->orWhere('ticket_number', 'like', "%{$this->search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }


        if ($this->filterOrg) {
            $query->where('organization_id', $this->filterOrg);
        }

        if ($this->filterDept) {
            $query->where('department_id', $this->filterDept);
        }

        if ($this->filterAssigned) {
            $query->where('owner_id', $this->filterAssigned);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Filter dropdown options based on user role
        $organizations = collect();
        $departments = collect();
        $agents = collect();

        if ($user->hasRole('admin')) {
            $organizations = Organization::orderBy('name')->get();
            $departments = Department::orderBy('name')->get();
            $agents = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['support', 'admin']);
            })->orderBy('name')->get();
        } elseif ($user->hasRole('support')) {
            // Agents see all organizations and departments in their group (or just their department)
            $organizations = Organization::orderBy('name')->get();
            
            if ($user->department?->department_group_id) {
                // Show all departments in the same group
                $departments = Department::where('department_group_id', $user->department->department_group_id)
                    ->orderBy('name')->get();
                $agents = User::whereHas('department', function ($q) use ($user) {
                    $q->where('department_group_id', $user->department->department_group_id);
                })->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['support', 'admin']);
                })->orderBy('name')->get();
            } else {
                // Fallback to just their department
                $departments = Department::where('id', $user->department_id)->get();
                $agents = User::where('department_id', $user->department_id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['support', 'admin']);
                    })->orderBy('name')->get();
            }
        } elseif ($user->hasRole('client')) {
            // Clients see only their organization
            $organizations = Organization::where('id', $user->organization_id)->get();
            $departments = Department::orderBy('name')->get(); // Clients can select departments for tickets
            $agents = collect(); // Clients don't see agent assignments
        }

        return view('livewire.manage-tickets', [
            'tickets' => $query->paginate(15),
            'organizations' => $organizations,
            'departments' => $departments,
            'agents' => $agents,
            'statusOptions' => TicketStatus::options(),
            'priorityOptions' => TicketPriority::options(),
            'showFilters' => $this->quickFilter === 'all',
        ]);
    }

    public function reopenTicket($ticketId)
    {
        try {
            $user = auth()->user();
            $ticket = Ticket::findOrFail($ticketId);
            
            // Check if user can update tickets
            if (!$user->can('tickets.update')) {
                session()->flash('error', 'You do not have permission to update tickets.');
                return;
            }
            
            // Check if user can access this ticket
            if (!$this->canAccessTicket($user, $ticket)) {
                session()->flash('error', 'You cannot update this ticket.');
                return;
            }

            // Check if ticket is actually closed
            if ($ticket->status !== 'closed') {
                session()->flash('error', 'Only closed tickets can be reopened.');
                return;
            }

            // Check reopen authorization based on policy
            if (!$user->can('reopen', $ticket)) {
                $reopenLimit = Setting::get('tickets.reopen_window_days', 3);
                session()->flash('error', 'Ticket closed more than ' . $reopenLimit . ' days ago. Please create a new ticket.');
                return;
            }
            
            // Reopen the ticket
            $ticket->update([
                'status' => 'open',
                'closed_at' => null
            ]);
            
            // Create system message for reopening
            \App\Models\TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => $user->id,
                'message' => "Ticket reopened by {$user->name} on " . now()->format('M d, Y \\a\\t H:i'),
                'is_internal' => false,
                'is_system_message' => true,
            ]);
            
            session()->flash('message', 'Ticket reopened successfully.');
            
        } catch (\Exception $e) {
            logger()->error('Failed to reopen ticket', [
                'ticket_id' => $ticketId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            session()->flash('error', 'Failed to reopen ticket.');
        }
    }
}
