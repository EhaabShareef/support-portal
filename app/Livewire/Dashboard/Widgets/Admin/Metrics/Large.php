<?php

namespace App\Livewire\Dashboard\Widgets\Admin\Metrics;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Large extends Component
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
            
            $this->metrics = Cache::remember("admin_metrics_large_{$user->id}", 300, function () {
                return [
                    'total_tickets' => Ticket::count(),
                    'open_tickets' => Ticket::where('status', '!=', 'closed')->count(),
                    'organizations' => Organization::where('is_active', true)->count(),
                    'active_users' => User::where('is_active', true)->count(),
                    'departments' => Department::count(),
                    'resolved_today' => Ticket::where('status', 'closed')
                        ->whereDate('updated_at', today())
                        ->count(),
                    'resolved_this_week' => Ticket::where('status', 'closed')
                        ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
                        ->count(),
                    'avg_resolution_time' => $this->getAverageResolutionTime(),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Admin Metrics Large widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function getAverageResolutionTime(): string
    {
        $resolvedTickets = Ticket::where('status', 'closed')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->whereDate('updated_at', '>=', now()->subDays(30))
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return 'N/A';
        }

        $totalMinutes = $resolvedTickets->sum(function ($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->updated_at);
        });

        $avgMinutes = $totalMinutes / $resolvedTickets->count();
        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.metrics.large');
    }
}