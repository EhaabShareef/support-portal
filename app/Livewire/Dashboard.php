<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $refreshInterval = 30000; // 30 seconds
    
    public function mount()
    {
        // Check user permissions
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Authentication required to access dashboard.');
        }
    }

    #[Computed]
    public function userRole()
    {
        return auth()->user()->roles->first()?->name ?? 'client';
    }

    #[Computed]
    public function dashboardData()
    {
        $user = auth()->user();
        $role = $this->userRole;
        
        return Cache::remember("dashboard_data_{$user->id}_{$role}", 300, function() use ($user, $role) {
            switch ($role) {
                case 'admin':
                    return $this->getAdminDashboardData($user);
                case 'support':
                    return $this->getAgentDashboardData($user);
                case 'client':
                default:
                    return $this->getClientDashboardData($user);
            }
        });
    }

    private function getAdminDashboardData($user)
    {
        return [
            'metrics' => [
                'total_tickets' => Ticket::count(),
                'open_tickets' => Ticket::where('status', '!=', 'closed')->count(),
                'resolved_today' => Ticket::whereDate('resolved_at', today())->count(),
                'organizations' => Organization::count(),
                'active_users' => User::where('is_active', true)->count(),
            ],
            'department_breakdown' => Department::withCount([
                'tickets',
                'tickets as open_tickets_count' => function($query) {
                    $query->where('status', '!=', 'closed');
                },
                'tickets as resolved_this_week_count' => function($query) {
                    $query->where('status', 'closed')
                          ->whereBetween('resolved_at', [now()->startOfWeek(), now()->endOfWeek()]);
                }
            ])->get(),
            'top_performers' => $this->getTopPerformers(),
            'contract_alerts' => $this->getContractAlerts(),
            'hardware_alerts' => $this->getHardwareAlerts(),
            'ticket_trends' => $this->getTicketTrends(30),
            'quick_actions' => [
                ['label' => 'Manage Users', 'route' => 'admin.users.index', 'icon' => 'users', 'color' => 'blue'],
                ['label' => 'Manage Roles', 'route' => 'admin.roles.index', 'icon' => 'shield-check', 'color' => 'purple'],
                ['label' => 'Organizations', 'route' => 'organizations.index', 'icon' => 'building-office-2', 'color' => 'green'],
                ['label' => 'Settings', 'route' => 'admin.settings', 'icon' => 'cog-6-tooth', 'color' => 'gray'],
            ],
        ];
    }

    private function getAgentDashboardData($user)
    {
        $departmentId = $user->department_id;
        
        return [
            'metrics' => [
                'my_tickets' => Ticket::where('assigned_to', $user->id)->count(),
                'my_open_tickets' => Ticket::where('assigned_to', $user->id)
                    ->where('status', '!=', 'closed')->count(),
                'department_tickets' => Ticket::where('department_id', $departmentId)->count(),
                'resolved_today' => Ticket::where('assigned_to', $user->id)
                    ->whereDate('resolved_at', today())->count(),
                'resolved_this_week' => Ticket::where('assigned_to', $user->id)
                    ->whereBetween('resolved_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ],
            'ticket_breakdown' => [
                'open' => Ticket::where('department_id', $departmentId)->where('status', 'open')->count(),
                'in_progress' => Ticket::where('department_id', $departmentId)->where('status', 'in_progress')->count(),
                'awaiting_customer_response' => Ticket::where('department_id', $departmentId)->where('status', 'awaiting_customer_response')->count(),
                'solution_provided' => Ticket::where('department_id', $departmentId)->where('status', 'solution_provided')->count(),
            ],
            'contribution_data' => $this->getContributionData($user->id),
            'department_ranking' => $this->getDepartmentRanking($user),
            'recent_activity' => $this->getRecentActivity($departmentId),
            'quick_actions' => [
                ['label' => 'Create Ticket', 'route' => 'tickets.create', 'icon' => 'plus-circle', 'color' => 'blue'],
                ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'ticket', 'color' => 'green'],
                ['label' => 'Department Tickets', 'route' => 'tickets.index', 'icon' => 'folder', 'color' => 'yellow'],
            ],
        ];
    }

    private function getClientDashboardData($user)
    {
        $organizationId = $user->organization_id;
        
        return [
            'metrics' => [
                'my_tickets' => Ticket::where('organization_id', $organizationId)->count(),
                'open_tickets' => Ticket::where('organization_id', $organizationId)
                    ->where('status', '!=', 'closed')->count(),
                'resolved_tickets' => Ticket::where('organization_id', $organizationId)
                    ->where('status', 'closed')->count(),
                'avg_response_time' => $this->getAverageResponseTime($organizationId),
            ],
            'ticket_breakdown' => [
                'open' => Ticket::where('organization_id', $organizationId)->where('status', 'open')->count(),
                'in_progress' => Ticket::where('organization_id', $organizationId)->where('status', 'in_progress')->count(),
                'awaiting_customer_response' => Ticket::where('organization_id', $organizationId)->where('status', 'awaiting_customer_response')->count(),
                'solution_provided' => Ticket::where('organization_id', $organizationId)->where('status', 'solution_provided')->count(),
                'closed' => Ticket::where('organization_id', $organizationId)->where('status', 'closed')->count(),
            ],
            'contracts' => OrganizationContract::where('organization_id', $organizationId)
                ->where('status', 'active')
                ->with('department')
                ->get(),
            'recent_tickets' => Ticket::where('organization_id', $organizationId)
                ->whereNotIn('status', ['closed', 'solution_provided']) // Hide closed tickets from recent list
                ->with(['department', 'assigned'])
                ->latest()
                ->limit(5)
                ->get(),
            'quick_actions' => [
                ['label' => 'Create Ticket', 'route' => 'tickets.create', 'icon' => 'plus-circle', 'color' => 'blue'],
                ['label' => 'My Tickets', 'route' => 'tickets.index', 'icon' => 'ticket', 'color' => 'green'],
            ],
        ];
    }

    private function getContributionData($userId)
    {
        $startDate = now()->subYear()->startOfDay();
        $endDate = now()->endOfDay();
        
        return DB::table('tickets')
            ->select(DB::raw('DATE(resolved_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('assigned_to', $userId)
            ->where('status', 'closed')
            ->whereBetween('resolved_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(resolved_at)'))
            ->get()
            ->keyBy('date')
            ->toArray();
    }

    private function getTopPerformers()
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        return Department::select('departments.*')
            ->withCount(['users as top_performer_count' => function($query) use ($startOfWeek, $endOfWeek) {
                $query->withCount(['assignedTickets as resolved_count' => function($q) use ($startOfWeek, $endOfWeek) {
                    $q->where('status', 'closed')
                      ->whereBetween('resolved_at', [$startOfWeek, $endOfWeek]);
                }]);
            }])
            ->with(['users' => function($query) use ($startOfWeek, $endOfWeek) {
                $query->withCount(['assignedTickets as resolved_count' => function($q) use ($startOfWeek, $endOfWeek) {
                    $q->where('status', 'closed')
                      ->whereBetween('resolved_at', [$startOfWeek, $endOfWeek]);
                }])
                ->orderBy('resolved_count', 'desc')
                ->limit(3);
            }])
            ->get();
    }

    private function getContractAlerts()
    {
        return OrganizationContract::where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30))
            ->with(['organization', 'department'])
            ->get();
    }

    private function getHardwareAlerts()
    {
        return OrganizationHardware::where('warranty_expiration', '<=', now()->addDays(30))
            ->orWhere('next_maintenance', '<=', now()->addDays(30))
            ->with(['organization'])
            ->get();
    }

    private function getTicketTrends($days = 30)
    {
        $startDate = now()->subDays($days)->startOfDay();
        
        return DB::table('tickets')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    private function getDepartmentRanking($user)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        
        $departmentAgents = User::where('department_id', $user->department_id)
            ->whereHas('roles', function($q) {
                $q->where('name', 'support');
            })
            ->withCount(['assignedTickets as resolved_count' => function($q) use ($startOfWeek, $endOfWeek) {
                $q->where('status', 'closed')
                  ->whereBetween('resolved_at', [$startOfWeek, $endOfWeek]);
            }])
            ->orderBy('resolved_count', 'desc')
            ->get();

        $userRank = $departmentAgents->search(function($agent) use ($user) {
            return $agent->id === $user->id;
        }) + 1;

        return [
            'user_rank' => $userRank,
            'total_agents' => $departmentAgents->count(),
            'user_resolved' => $departmentAgents->where('id', $user->id)->first()?->resolved_count ?? 0,
            'top_agent_resolved' => $departmentAgents->first()?->resolved_count ?? 0,
        ];
    }

    private function getRecentActivity($departmentId)
    {
        return Ticket::where('department_id', $departmentId)
            ->whereNotIn('status', ['closed', 'solution_provided']) // Hide closed tickets from recent activity
            ->with(['client', 'assigned'])
            ->latest()
            ->limit(10)
            ->get();
    }

    private function getAverageResponseTime($organizationId)
    {
        $avgMinutes = Ticket::where('organization_id', $organizationId)
            ->whereNotNull('first_response_at')
            ->avg('response_time_minutes');

        if (!$avgMinutes) return 'N/A';

        $hours = floor($avgMinutes / 60);
        $minutes = $avgMinutes % 60;

        return $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
    }

    public function refreshData()
    {
        $user = auth()->user();
        Cache::forget("dashboard_data_{$user->id}_{$this->userRole}");
        $this->dispatch('dataRefreshed');
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'dashboardData' => $this->dashboardData,
            'userRole' => $this->userRole,
        ]);
    }
}
