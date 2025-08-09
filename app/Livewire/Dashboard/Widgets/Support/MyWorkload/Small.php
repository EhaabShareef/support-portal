<?php

namespace App\Livewire\Dashboard\Widgets\Support\MyWorkload;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Small extends Component
{
    public $workloadData = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;

    public function mount(): void
    {
        // Check permissions before loading data
        $user = Auth::user();
        if (!$user || !$user->can('dashboard.support')) {
            abort(403, 'Insufficient permissions to view this widget.');
        }
        
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            
            $this->workloadData = Cache::remember("support_workload_small_{$user->id}", 300, function () use ($user) {
                $assignedTickets = Ticket::where('assigned_to', $user->id);
                $openTickets = clone $assignedTickets;
                
                return [
                    'total_assigned' => $assignedTickets->count(),
                    'open_assigned' => $openTickets->whereNotIn('status', ['closed', 'resolved'])->count(),
                    'high_priority' => Ticket::where('assigned_to', $user->id)
                        ->where('priority', 'high')
                        ->whereNotIn('status', ['closed', 'resolved'])
                        ->count(),
                    'resolved_today' => Ticket::where('assigned_to', $user->id)
                        ->whereIn('status', ['closed', 'resolved'])
                        ->whereDate('updated_at', today())
                        ->count(),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Support My Workload Small widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("support_workload_small_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.support.my-workload.small');
    }
}