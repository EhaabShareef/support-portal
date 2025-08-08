<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Organization;
use App\Models\Contract;
use App\Models\Hardware;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OrganizationSummaryReport extends Component
{
    use WithPagination;

    // Filter properties
    public $subscriptionStatus = '';
    public $isActive = '';
    public $search = '';

    protected $queryString = [
        'subscriptionStatus',
        'isActive',
        'search' => ['except' => '']
    ];

    public function mount()
    {
        // Check admin authorization
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to reports.');
        }
    }

    public function getOrganizationSummariesProperty()
    {
        $query = Organization::query()
            ->select([
                'organizations.id',
                'organizations.name',
                'organizations.subscription_status',
                'organizations.is_active',
                'organizations.created_at'
            ])
            ->with([
                'users' => function($q) {
                    $q->where('is_active', true);
                },
                'contracts' => function($q) {
                    $q->where('status', 'Active');
                },
                'hardware',
                'tickets' => function($q) {
                    $q->whereIn('status', ['Open', 'In Progress', 'Pending']);
                }
            ])
            ->withCount([
                'users as active_users_count' => function($q) {
                    $q->where('is_active', true);
                },
                'contracts as active_contracts_count' => function($q) {
                    $q->where('status', 'Active');
                },
                'hardware as hardware_count',
                'tickets as open_tickets_count' => function($q) {
                    $q->whereIn('status', ['Open', 'In Progress', 'Pending']);
                }
            ]);

        // Apply filters
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->subscriptionStatus) {
            $query->where('subscription_status', $this->subscriptionStatus);
        }

        if ($this->isActive !== '') {
            $query->where('is_active', $this->isActive);
        }

        return $query->orderBy('name')->paginate(25);
    }

    public function getTotalStatsProperty()
    {
        $query = Organization::query();

        // Apply same filters as main query for consistency
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->subscriptionStatus) {
            $query->where('subscription_status', $this->subscriptionStatus);
        }

        if ($this->isActive !== '') {
            $query->where('is_active', $this->isActive);
        }

        $organizationIds = $query->pluck('id');

        return [
            'total_organizations' => $organizationIds->count(),
            'total_users' => User::whereIn('organization_id', $organizationIds)->where('is_active', true)->count(),
            'total_contracts' => Contract::whereIn('organization_id', $organizationIds)->where('status', 'Active')->count(),
            'total_hardware' => Hardware::whereIn('organization_id', $organizationIds)->count(),
            'total_open_tickets' => Ticket::whereIn('organization_id', $organizationIds)
                ->whereIn('status', ['Open', 'In Progress', 'Pending'])->count()
        ];
    }

    public function resetFilters()
    {
        $this->subscriptionStatus = '';
        $this->isActive = '';
        $this->search = '';
        $this->resetPage();
    }

    public function export()
    {
        // Future: implement CSV/Excel export
        session()->flash('message', 'Export functionality will be implemented in a future update.');
    }

    public function render()
    {
        return view('livewire.admin.reports.organization-summary-report', [
            'organizations' => $this->organizationSummaries,
            'totalStats' => $this->totalStats,
            'subscriptionStatuses' => ['Trial', 'Active', 'Suspended', 'Cancelled'],
            'activeStatuses' => [
                1 => 'Active',
                0 => 'Inactive'
            ]
        ])->title('Organization Summary Report');
    }
}