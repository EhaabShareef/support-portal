<?php

namespace App\Livewire\Dashboard\Widgets\Admin\TicketAnalytics;

use App\Models\Ticket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Small extends Component
{
    public $analytics = [];
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
            
            $this->analytics = Cache::remember("ticket_analytics_small_{$user->id}", 300, function () {
                $today = now()->startOfDay();
                $yesterday = now()->subDay()->startOfDay();
                
                $todayCount = Ticket::whereDate('created_at', $today)->count();
                $yesterdayCount = Ticket::whereDate('created_at', $yesterday)->count();
                
                // Calculate trend
                $trend = 'stable';
                $trendPercent = 0;
                
                if ($yesterdayCount > 0) {
                    $trendPercent = round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 1);
                    if ($trendPercent > 10) {
                        $trend = 'up';
                    } elseif ($trendPercent < -10) {
                        $trend = 'down';
                    }
                }
                
                return [
                    'total_tickets' => Ticket::count(),
                    'today_tickets' => $todayCount,
                    'open_tickets' => Ticket::whereNotIn('status', ['closed', 'resolved'])->count(),
                    'trend' => $trend,
                    'trend_percent' => abs($trendPercent),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Ticket Analytics Small widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function refreshData(): void
    {
        $this->dataLoaded = false;
        Cache::forget("ticket_analytics_small_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.ticket-analytics.small');
    }
}