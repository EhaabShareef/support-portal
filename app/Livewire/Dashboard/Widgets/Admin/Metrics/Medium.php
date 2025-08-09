<?php

namespace App\Livewire\Dashboard\Widgets\Admin\Metrics;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Medium extends Component
{
    public $metrics = [];
    public bool $dataLoaded = false;
    public bool $hasError = false;

    public function mount(): void
    {
        // Check permissions before loading data
        $user = Auth::user();
        if (!$user || !$user->can('dashboard.admin')) {
            abort(403, 'Insufficient permissions to view this widget.');
        }
        
        $this->loadData();
    }

    public function loadData(): void
    {
        try {
            $user = Auth::user();
            
            $this->metrics = Cache::remember("admin_metrics_medium_{$user->id}", 300, function () {
                return [
                    'total_tickets' => Ticket::count(),
                    'open_tickets' => Ticket::where('status', '!=', 'closed')->count(),
                    'organizations' => Organization::where('is_active', true)->count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'resolved_today' => Ticket::where('status', 'closed')
                        ->whereDate('updated_at', today())
                        ->count(),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Admin Metrics Medium widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.metrics.medium');
    }
}