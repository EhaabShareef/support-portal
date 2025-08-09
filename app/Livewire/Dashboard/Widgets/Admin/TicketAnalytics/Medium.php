<?php

namespace App\Livewire\Dashboard\Widgets\Admin\TicketAnalytics;

use App\Models\Ticket;
use App\Models\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Medium extends Component
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
            
            $this->analytics = Cache::remember("ticket_analytics_medium_{$user->id}", 300, function () {
                $today = now()->startOfDay();
                $yesterday = now()->subDay()->startOfDay();
                $thisWeek = now()->startOfWeek();
                $lastWeek = now()->subWeek()->startOfWeek();
                
                // Basic counts
                $todayCount = Ticket::whereDate('created_at', $today)->count();
                $yesterdayCount = Ticket::whereDate('created_at', $yesterday)->count();
                $thisWeekCount = Ticket::whereBetween('created_at', [$thisWeek, now()])->count();
                $lastWeekCount = Ticket::whereBetween('created_at', [$lastWeek, $thisWeek])->count();
                
                // Status breakdown
                $statusBreakdown = Ticket::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
                    ->pluck('count', 'status')
                    ->toArray();
                
                // Priority breakdown
                $priorityBreakdown = Ticket::select('priority', DB::raw('count(*) as count'))
                    ->groupBy('priority')
                    ->get()
                    ->pluck('count', 'priority')
                    ->toArray();
                
                // Department performance
                $departmentStats = Department::withCount([
                    'tickets',
                    'tickets as open_tickets_count' => function($query) {
                        $query->whereNotIn('status', ['closed', 'resolved']);
                    }
                ])->get()->map(function($dept) {
                    return [
                        'name' => $dept->name,
                        'total' => $dept->tickets_count,
                        'open' => $dept->open_tickets_count,
                    ];
                });
                
                // Calculate trends
                $dailyTrend = $yesterdayCount > 0 ? 
                    round((($todayCount - $yesterdayCount) / $yesterdayCount) * 100, 1) : 0;
                $weeklyTrend = $lastWeekCount > 0 ? 
                    round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1) : 0;
                
                return [
                    'today_tickets' => $todayCount,
                    'this_week_tickets' => $thisWeekCount,
                    'daily_trend' => $dailyTrend,
                    'weekly_trend' => $weeklyTrend,
                    'total_tickets' => Ticket::count(),
                    'status_breakdown' => $statusBreakdown,
                    'priority_breakdown' => $priorityBreakdown,
                    'department_stats' => $departmentStats,
                    'avg_resolution_time' => $this->getAverageResolutionTime(),
                ];
            });

            $this->dataLoaded = true;
            $this->hasError = false;
        } catch (\Exception $e) {
            $this->hasError = true;
            logger()->error("Ticket Analytics Medium widget failed to load", [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
        }
    }

    private function getAverageResolutionTime(): string
    {
        $resolvedTickets = Ticket::whereIn('status', ['closed', 'resolved'])
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
        Cache::forget("ticket_analytics_medium_" . Auth::id());
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.admin.ticket-analytics.medium');
    }
}