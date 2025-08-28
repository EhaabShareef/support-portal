<?php

namespace App\Livewire;

use App\Models\OrganizationHardware;
use Livewire\Component;
use Livewire\WithPagination;

class HardwareRelatedTickets extends Component
{
    use WithPagination;

    public OrganizationHardware $hardware;
    public string $statusFilter = '';
    public string $dateFilter = '';

    public function mount(OrganizationHardware $hardware): void
    {
        $this->hardware = $hardware;
    }

    public function getRelatedTicketsProperty()
    {
        $query = $this->hardware->tickets()
            ->with(['organization', 'department', 'client', 'owner'])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if (!empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        // Apply date filter
        if (!empty($this->dateFilter)) {
            switch ($this->dateFilter) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        return $query->paginate(10);
    }

    public function render()
    {
        return view('livewire.hardware-related-tickets', [
            'tickets' => $this->relatedTickets,
            'statusOptions' => [
                '' => 'All Statuses',
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'pending' => 'Pending',
                'resolved' => 'Resolved',
                'closed' => 'Closed'
            ],
            'dateOptions' => [
                '' => 'All Time',
                'today' => 'Today',
                'week' => 'This Week',
                'month' => 'This Month',
                'year' => 'This Year'
            ]
        ]);
    }
}
