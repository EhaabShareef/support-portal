<?php

namespace App\Livewire\Dashboard\Widgets\HardwareProgress;

use App\Models\Ticket;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HardwareProgressWidget extends Component
{
    public $size = 'medium';
    public $organizationId = null;
    public $showOrganizationFilter = false;
    public $hasError = false;
    public $dataLoaded = false;
    public $data = [];
    public $organizationBreakdown = [];

    protected $listeners = ['refreshWidget' => 'loadData'];

    public function mount($size = 'medium', $params = [])
    {
        $this->size = $size;
        $this->organizationId = $params['organization_id'] ?? null;
        $this->showOrganizationFilter = $size === 'large';
        $this->loadData();
    }

    public function loadData()
    {
        try {
            $this->data = $this->getHardwareProgressData();
            $this->organizationBreakdown = $this->getOrganizationBreakdown();
            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            $this->dataLoaded = false;
        }
    }

    public function getHardwareProgressData()
    {
        $query = Ticket::whereHas('department.departmentGroup', function ($q) {
            $q->where('name', 'Hardware');
        })
        ->where('status', '!=', 'closed')
        ->with(['hardware', 'organization']);

        // Filter by organization if specified
        if ($this->organizationId) {
            $query->where('organization_id', $this->organizationId);
        }

        // For clients, only show their organization's tickets
        if (auth()->user()->hasRole('client')) {
            $query->where('organization_id', auth()->user()->organization_id);
        }

        $tickets = $query->get();

        // Calculate overall progress
        $totalTickets = $tickets->count();
        $totalHardwareUnits = 0;
        $totalFixedUnits = 0;
        $totalPendingUnits = 0;

        foreach ($tickets as $ticket) {
            $progress = $ticket->hardware_progress;
            $totalHardwareUnits += $progress['total'];
            $totalFixedUnits += $progress['fixed'];
            $totalPendingUnits += $progress['pending'];
        }

        $overallPercentage = $totalHardwareUnits > 0 ? round(($totalFixedUnits / $totalHardwareUnits) * 100) : 0;

        return [
            'total_tickets' => $totalTickets,
            'total_units' => $totalHardwareUnits,
            'fixed_units' => $totalFixedUnits,
            'pending_units' => $totalPendingUnits,
            'percentage' => $overallPercentage,
            'tickets' => $tickets
        ];
    }

    public function getOrganizationBreakdown()
    {
        if (!auth()->user()->hasRole(['admin', 'support'])) {
            return collect();
        }

        return Organization::whereHas('tickets', function ($query) {
            $query->whereHas('department.departmentGroup', function ($q) {
                $q->where('name', 'Hardware');
            })
            ->where('status', '!=', 'closed');
        })
        ->with(['tickets' => function ($query) {
            $query->whereHas('department.departmentGroup', function ($q) {
                $q->where('name', 'Hardware');
            })
            ->where('status', '!=', 'closed')
            ->with('hardware');
        }])
        ->get()
        ->map(function ($organization) {
            $totalUnits = 0;
            $fixedUnits = 0;
            $pendingUnits = 0;

            foreach ($organization->tickets as $ticket) {
                $progress = $ticket->hardware_progress;
                $totalUnits += $progress['total'];
                $fixedUnits += $progress['fixed'];
                $pendingUnits += $progress['pending'];
            }

            $percentage = $totalUnits > 0 ? round(($fixedUnits / $totalUnits) * 100) : 0;

            return [
                'name' => $organization->name,
                'total_tickets' => $organization->tickets->count(),
                'total_units' => $totalUnits,
                'fixed_units' => $fixedUnits,
                'pending_units' => $pendingUnits,
                'percentage' => $percentage
            ];
        })
        ->sortByDesc('total_units');
    }

    public function refreshData()
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.hardware-progress.' . $this->size);
    }
}
