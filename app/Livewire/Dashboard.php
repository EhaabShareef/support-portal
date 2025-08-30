<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use App\Models\OrganizationContract;
use App\Models\OrganizationHardware;
use App\Models\DashboardWidget;
use App\Models\UserWidgetSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $refreshInterval = 30000; // 30 seconds
    public $showNewFeaturesBanner = true;
    
    public function mount()
    {
        // Check user permissions
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Authentication required to access dashboard.');
        }
        
        // Check role-based dashboard permissions
        $userRole = $user->roles->first()?->name ?? 'client';
        $requiredPermission = "dashboard.{$userRole}";
        
        if (!$user->can('dashboard.access') || !$user->can($requiredPermission)) {
            abort(403, 'Insufficient permissions to access dashboard.');
        }
        
        // Check if user has dismissed the new features banner
        $this->showNewFeaturesBanner = !session('new_features_banner_dismissed', false);
    }

    /**
     * Get user's visible widgets in order
     */
    #[Computed]
    public function userWidgets()
    {
        $user = Auth::user();
        $userRole = $user->roles->first()?->name ?? 'client';
        
        // Get available widgets for user's role with proper permission filtering and eager loading
        $availableWidgets = DashboardWidget::where('is_active', true)
            ->where('category', $userRole)
            ->with(['userSettings' => function($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($widget) use ($user) {
                return $widget->isVisibleForUser($user);
            });
        
        // Get user's widget settings with eager loading
        $userSettings = UserWidgetSetting::where('user_id', $user->id)
            ->with('widget')
            ->get()
            ->keyBy('widget_id');
        
        // Combine widgets with user settings
        $widgets = $availableWidgets->map(function ($widget) use ($userSettings) {
            $setting = $userSettings->get($widget->id);
            
            return (object) [
                'widget' => $widget,
                'is_visible' => $setting ? $setting->is_visible : $widget->is_default_visible,
                'size' => $setting ? $setting->size : $widget->default_size,
                'sort_order' => $setting ? $setting->sort_order : $widget->sort_order,
            ];
        })->filter(function ($item) {
            return $item->is_visible;
        })->sortBy([
            ['sort_order', 'asc'],
            ['widget.id', 'asc'], 
            ['widget.name', 'asc']
        ]);
        
        return $widgets;
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
                ['label' => 'Settings', 'route' => 'settings', 'icon' => 'cog-6-tooth', 'color' => 'gray'],
            ],
        ];
    }

    private function getAgentDashboardData($user)
    {
        $departmentId = $user->department_id;
        
        return [
            'metrics' => [
                'my_tickets' => Ticket::where('owner_id', $user->id)->count(),
                'my_open_tickets' => Ticket::where('owner_id', $user->id)
                    ->where('status', '!=', 'closed')->count(),
                'department_tickets' => Ticket::where('department_id', $departmentId)->count(),
                'resolved_today' => Ticket::where('owner_id', $user->id)
                    ->whereDate('resolved_at', today())->count(),
                'resolved_this_week' => Ticket::where('owner_id', $user->id)
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
                ->with(['department', 'owner'])
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
            ->where('owner_id', $userId)
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
        return OrganizationHardware::where('next_maintenance', '<=', now()->addDays(30))
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
            ->with(['client', 'owner'])
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
        
        // Refresh all widgets
        $this->dispatch('refreshAllWidgets');
    }

    /**
     * Open the customize dashboard modal
     */
    public function openCustomizeModal()
    {
        $this->dispatch('open-customize');
    }

    /**
     * Get CSS classes for widget containers based on size
     */
    public function getWidgetClasses(string $size): string
    {
        $sizeClasses = [
            '1x1' => 'col-span-1 row-span-1',
            '2x1' => 'col-span-1 md:col-span-2 row-span-1',
            '2x2' => 'col-span-1 md:col-span-2 row-span-2',
            '3x2' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-2',
            '3x3' => 'col-span-1 md:col-span-2 lg:col-span-3 row-span-3',
        ];

        return $sizeClasses[$size] ?? $sizeClasses['1x1'];
    }

    /**
     * Dismiss the new features banner
     */
    public function dismissNewFeaturesBanner()
    {
        session(['new_features_banner_dismissed' => true]);
        $this->showNewFeaturesBanner = false;
    }

    /**
     * Listen for widget customization events
     */
    public function getListeners()
    {
        return [
            'widgets-updated' => '$refresh',
        ];
    }

    public function render()
    {
        // Emit ready event for loading overlay after rendering
        $this->dispatch('dashboard-ready');
        
        return view('livewire.dashboard', [
            'dashboardData' => $this->dashboardData,
            'userRole' => $this->userRole,
            'userWidgets' => $this->userWidgets,
        ]);
    }
}
