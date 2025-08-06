<?php

namespace App\Livewire;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Department;
use App\Models\Organization;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ManageTickets extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterStatus = '';

    public string $filterPriority = '';

    public string $filterType = '';

    public string $filterOrg = '';

    public string $filterDept = '';

    public string $filterAssigned = '';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public string $quickFilter = 'all'; // 'all', 'my_tickets', 'my_department', 'unassigned'

    public bool $showClosed = false; // Toggle to show/hide closed and solution_provided tickets

    // Form properties for creating tickets
    public bool $showCreateModal = false;

    public array $form = [
        'subject' => '',
        'type' => 'task',
        'priority' => 'normal',
        'description' => '',
        'organization_id' => '',
        'client_id' => '',
        'department_id' => '',
    ];

    protected $rules = [
        'form.subject' => 'required|string|max:255',
        'form.type' => 'required|in:issue,feedback,bug,lead,task,incident,request',
        'form.priority' => 'required|in:low,normal,high,urgent,critical',
        'form.description' => 'nullable|string',
        'form.organization_id' => 'required|exists:organizations,id',
        'form.client_id' => 'required|exists:users,id',
        'form.department_id' => 'required|exists:departments,id',
    ];

    public function updating($field)
    {
        if (in_array($field, ['search', 'filterStatus', 'filterPriority', 'filterType', 'filterOrg', 'filterDept', 'filterAssigned', 'quickFilter', 'showClosed'])) {
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
        return auth()->user()->hasRole('Super Admin') || auth()->user()->can('tickets.create');
    }


    public function openCreateModal()
    {
        $this->resetForm();

        // Auto-set organization for clients
        $user = auth()->user();
        if ($user->hasRole('Client')) {
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
            'type' => 'task',
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
                $q->where('name', 'Client');
            })
            ->orderBy('name')
            ->get();
    }

    public function save()
    {
        $user = auth()->user();
        $this->validate();

        // Set defaults for new tickets
        $ticketData = $this->form;
        $ticketData['status'] = 'open';
        $ticketData['assigned_to'] = null; // Keep unassigned

        // Enforce security constraints based on user role
        if ($user->hasRole('Client')) {
            // Clients can only create tickets for their own organization
            $ticketData['organization_id'] = $user->organization_id;
            $ticketData['client_id'] = $user->id;
        } elseif ($user->hasRole('Agent')) {
            // Agents create tickets with proper organization/client assignment
            $ticketData['client_id'] = $user->id; // Default to agent as client unless specified
        }

        Ticket::create($ticketData);
        session()->flash('message', 'Ticket created successfully.');

        $this->closeModal();
    }


    public function render()
    {
        $user = auth()->user();
        $query = Ticket::query()->with(['organization', 'department', 'client', 'assigned'])
            ->withCount('messages')
            ->with(['messages' => function($q) {
                $q->latest()->limit(1);
            }]);

        // Apply quick filter first
        switch ($this->quickFilter) {
            case 'my_tickets':
                $query->where('assigned_to', $user->id);
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
                $query->whereNull('assigned_to');
                if ($user->hasRole('Agent')) {
                    // Allow agents to see unassigned tickets from their department group
                    if ($user->department?->department_group_id) {
                        $query->forDepartmentGroup($user->department->department_group_id);
                    } else {
                        $query->where('department_id', $user->department_id);
                    }
                } elseif ($user->hasRole('Client')) {
                    $query->where('organization_id', $user->organization_id);
                }
                break;
            case 'all':
            default:
                // Apply role-based filtering for 'all' tab
                if ($user->hasRole('Agent')) {
                    // Agents can see tickets in their department group
                    if ($user->department?->department_group_id) {
                        $query->forDepartmentGroup($user->department->department_group_id);
                    } else {
                        $query->where('department_id', $user->department_id);
                    }
                } elseif ($user->hasRole('Client')) {
                    // Clients can only see tickets from their organization
                    $query->where('organization_id', $user->organization_id);
                }
                // Super Admin and Admin can see all tickets (no additional filtering)
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

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterOrg) {
            $query->where('organization_id', $this->filterOrg);
        }

        if ($this->filterDept) {
            $query->where('department_id', $this->filterDept);
        }

        if ($this->filterAssigned) {
            $query->where('assigned_to', $this->filterAssigned);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        // Filter dropdown options based on user role
        $organizations = collect();
        $departments = collect();
        $agents = collect();

        if ($user->hasRole('Super Admin') || $user->hasRole('Admin')) {
            $organizations = Organization::orderBy('name')->get();
            $departments = Department::orderBy('name')->get();
            $agents = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Agent', 'Admin', 'Super Admin']);
            })->orderBy('name')->get();
        } elseif ($user->hasRole('Agent')) {
            // Agents see all organizations and departments in their group (or just their department)
            $organizations = Organization::orderBy('name')->get();
            
            if ($user->department?->department_group_id) {
                // Show all departments in the same group
                $departments = Department::where('department_group_id', $user->department->department_group_id)
                    ->orderBy('name')->get();
                $agents = User::whereHas('department', function ($q) use ($user) {
                    $q->where('department_group_id', $user->department->department_group_id);
                })->whereHas('roles', function ($q) {
                    $q->whereIn('name', ['Agent', 'Admin', 'Super Admin']);
                })->orderBy('name')->get();
            } else {
                // Fallback to just their department
                $departments = Department::where('id', $user->department_id)->get();
                $agents = User::where('department_id', $user->department_id)
                    ->whereHas('roles', function ($q) {
                        $q->whereIn('name', ['Agent', 'Admin', 'Super Admin']);
                    })->orderBy('name')->get();
            }
        } elseif ($user->hasRole('Client')) {
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
            'typeOptions' => [
                'issue' => 'Issue',
                'feedback' => 'Feedback',
                'bug' => 'Bug',
                'lead' => 'Lead',
                'task' => 'Task',
                'incident' => 'Incident',
                'request' => 'Request',
            ],
            'showFilters' => $this->quickFilter === 'all',
        ]);
    }
}
