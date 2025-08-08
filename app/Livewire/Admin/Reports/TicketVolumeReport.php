<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Ticket;
use App\Models\Organization;
use App\Models\Department;
use App\Models\DepartmentGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class TicketVolumeReport extends Component
{
    use WithPagination;

    // Filter properties
    public $startDate;
    public $endDate;
    public $organizationId = '';
    public $departmentId = '';
    public $departmentGroupId = '';
    public $assignedUserId = '';
    public $ticketType = '';
    public $priority = '';
    public $status = '';
    public $groupBy = 'status'; // status, priority, type, date
    public $dateGrouping = 'daily'; // daily, weekly, monthly

    protected $queryString = [
        'startDate',
        'endDate', 
        'organizationId',
        'departmentId',
        'departmentGroupId',
        'assignedUserId',
        'ticketType',
        'priority',
        'status',
        'groupBy',
        'dateGrouping'
    ];

    public function mount()
    {
        // Check admin authorization
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to reports.');
        }

        // Set default date range to last 30 days
        $this->startDate = $this->startDate ?: now()->subDays(30)->format('Y-m-d');
        $this->endDate = $this->endDate ?: now()->format('Y-m-d');
    }

    public function getTicketVolumeDataProperty()
    {
        $query = Ticket::query()
            ->with(['organization:id,name', 'assignedUser:id,name', 'department:id,name'])
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        // Apply filters
        $this->applyFilters($query);

        // Group by selected criteria
        switch ($this->groupBy) {
            case 'status':
                return $query->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->orderBy('count', 'desc')
                    ->get();

            case 'priority':
                return $query->selectRaw('priority, COUNT(*) as count')
                    ->groupBy('priority')
                    ->orderBy('count', 'desc')
                    ->get();

            case 'type':
                return $query->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->orderBy('count', 'desc')
                    ->get();

            case 'date':
                $dateFormat = match($this->dateGrouping) {
                    'daily' => '%Y-%m-%d',
                    'weekly' => '%Y-%u',
                    'monthly' => '%Y-%m',
                    default => '%Y-%m-%d'
                };

                return $query->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period, COUNT(*) as count")
                    ->groupBy('period')
                    ->orderBy('period')
                    ->get();

            default:
                return $query->selectRaw('status, COUNT(*) as count')
                    ->groupBy('status')
                    ->orderBy('count', 'desc')
                    ->get();
        }
    }

    public function getTotalTicketsProperty()
    {
        $query = Ticket::query()
            ->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);

        $this->applyFilters($query);
        
        return $query->count();
    }

    public function getOrganizationsProperty()
    {
        return Organization::active()->ordered()->get();
    }

    public function getDepartmentGroupsProperty()
    {
        return DepartmentGroup::active()->ordered()->get();
    }

    public function getDepartmentsProperty()
    {
        $query = Department::active()->ordered();
        
        if ($this->departmentGroupId) {
            $query->where('department_group_id', $this->departmentGroupId);
        }
        
        return $query->get();
    }

    public function getAgentsProperty()
    {
        return User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['admin', 'support']);
        })->orderBy('name')->get();
    }

    private function applyFilters($query)
    {
        if ($this->organizationId) {
            $query->where('organization_id', $this->organizationId);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->departmentGroupId && !$this->departmentId) {
            $query->whereHas('department', function ($q) {
                $q->where('department_group_id', $this->departmentGroupId);
            });
        }

        if ($this->assignedUserId) {
            $query->where('assigned_user_id', $this->assignedUserId);
        }

        if ($this->ticketType) {
            $query->where('type', $this->ticketType);
        }

        if ($this->priority) {
            $query->where('priority', $this->priority);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }
    }

    public function updatedDepartmentGroupId()
    {
        $this->departmentId = '';
    }

    public function resetFilters()
    {
        $this->organizationId = '';
        $this->departmentId = '';
        $this->departmentGroupId = '';
        $this->assignedUserId = '';
        $this->ticketType = '';
        $this->priority = '';
        $this->status = '';
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function export()
    {
        // Future: implement CSV/Excel export
        session()->flash('message', 'Export functionality will be implemented in a future update.');
    }

    public function render()
    {
        return view('livewire.admin.reports.ticket-volume-report', [
            'ticketData' => $this->ticketVolumeData,
            'totalTickets' => $this->totalTickets,
            'organizations' => $this->organizations,
            'departmentGroups' => $this->departmentGroups,
            'departments' => $this->departments,
            'agents' => $this->agents,
            'ticketTypes' => ['Support', 'Bug Report', 'Feature Request', 'Hardware Issue', 'Software Issue'],
            'priorities' => ['Low', 'Medium', 'High', 'Critical'],
            'statuses' => ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed']
        ])->title('Ticket Volume & Status Trends Report');
    }
}