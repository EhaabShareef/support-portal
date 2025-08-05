{{-- Agent Dashboard --}}

{{-- Key Metrics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">My Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    {{ number_format($dashboardData['metrics']['my_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-user class="h-8 w-8 text-blue-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-red-600 dark:text-red-400 font-medium">
                {{ $dashboardData['metrics']['my_open_tickets'] }} still open
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Resolved Today</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                    {{ number_format($dashboardData['metrics']['resolved_today']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">
                {{ $dashboardData['metrics']['resolved_this_week'] }} this week
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Department Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                    {{ number_format($dashboardData['metrics']['department_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-building-office class="h-8 w-8 text-purple-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                Total department load
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">My Ranking</p>
                <p class="text-2xl sm:text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                    #{{ $dashboardData['department_ranking']['user_rank'] }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-trophy class="h-8 w-8 text-amber-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                of {{ $dashboardData['department_ranking']['total_agents'] }} agents
            </span>
        </div>
    </div>
</div>

{{-- GitHub-style Contribution Graph --}}
<div class="dashboard-card">
    <div class="card-header">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
            <x-heroicon-o-fire class="h-5 w-5 text-green-500" />
            Ticket Resolution Activity
        </h3>
        <span class="text-sm text-neutral-500 dark:text-neutral-400">Past 12 months</span>
    </div>
    <div class="contribution-graph-container overflow-x-auto">
        <div id="contributionGraph" class="contribution-graph"></div>
    </div>
    <div class="flex items-center justify-between mt-4 text-sm text-neutral-600 dark:text-neutral-400">
        <span>Less</span>
        <div class="flex items-center gap-1">
            <div class="contribution-level-0"></div>
            <div class="contribution-level-1"></div>
            <div class="contribution-level-2"></div>
            <div class="contribution-level-3"></div>
            <div class="contribution-level-4"></div>
        </div>
        <span>More</span>
    </div>
</div>

{{-- Ticket Status Breakdown and Performance --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Ticket Status Breakdown --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-chart-pie class="h-5 w-5 text-blue-500" />
                Department Ticket Status
            </h3>
        </div>
        <div class="space-y-4">
            @php
                $statusConfig = [
                    'open' => ['color' => 'red', 'icon' => 'exclamation-circle', 'label' => 'Open'],
                    'in_progress' => ['color' => 'yellow', 'icon' => 'clock', 'label' => 'In Progress'],
                    'awaiting_customer_response' => ['color' => 'blue', 'icon' => 'chat-bubble-left-right', 'label' => 'Awaiting Response'],
                    'solution_provided' => ['color' => 'green', 'icon' => 'check', 'label' => 'Solution Provided'],
                ];
                $totalTickets = array_sum($dashboardData['ticket_breakdown']);
            @endphp

            @foreach($dashboardData['ticket_breakdown'] as $status => $count)
                @php
                    $config = $statusConfig[$status] ?? ['color' => 'gray', 'icon' => 'question-mark-circle', 'label' => ucfirst($status)];
                    $percentage = $totalTickets > 0 ? round(($count / $totalTickets) * 100, 1) : 0;
                @endphp
                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        @php $iconComponent = 'heroicon-o-' . $config['icon']; @endphp
                        <x-dynamic-component :component="$iconComponent" class="h-5 w-5 text-{{ $config['color'] }}-500" />
                        <span class="font-medium text-sm text-neutral-800 dark:text-neutral-200">{{ $config['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-20 bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                            <div class="bg-{{ $config['color'] }}-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 min-w-[3rem] text-right">
                            {{ $count }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Performance Stats --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-chart-bar-square class="h-5 w-5 text-green-500" />
                My Performance
            </h3>
        </div>
        <div class="space-y-4">
            <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-700/50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">This Week</span>
                    <x-heroicon-o-fire class="h-4 w-4 text-green-600 dark:text-green-400" />
                </div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-300">
                    {{ $dashboardData['department_ranking']['user_resolved'] }} resolved
                </div>
                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                    Top performer: {{ $dashboardData['department_ranking']['top_agent_resolved'] }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
                    <div class="text-xs font-medium text-blue-600 dark:text-blue-400 mb-1">Rank</div>
                    <div class="text-lg font-bold text-blue-700 dark:text-blue-300">
                        #{{ $dashboardData['department_ranking']['user_rank'] }}
                    </div>
                </div>
                <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-700/50">
                    <div class="text-xs font-medium text-purple-600 dark:text-purple-400 mb-1">Total</div>
                    <div class="text-lg font-bold text-purple-700 dark:text-purple-300">
                        {{ $dashboardData['metrics']['my_tickets'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Activity and Quick Actions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Recent Activity --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-gray-500" />
                Recent Department Activity
            </h3>
        </div>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @foreach($dashboardData['recent_activity'] as $ticket)
                <div class="flex items-start gap-3 p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <div class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                {{ $ticket->ticket_number }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                            </span>
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400 truncate">
                            {{ $ticket->subject }}
                        </div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-500 mt-1">
                            {{ $ticket->client->name }} • {{ $ticket->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-rocket-launch class="h-5 w-5 text-purple-500" />
                Quick Actions
            </h3>
        </div>
        <div class="space-y-3">
            @foreach($dashboardData['quick_actions'] as $action)
                <a href="{{ route($action['route']) }}" 
                   class="flex items-center gap-3 p-4 bg-gradient-to-r from-{{ $action['color'] }}-50 to-{{ $action['color'] }}-100 
                          dark:from-{{ $action['color'] }}-900/20 dark:to-{{ $action['color'] }}-800/20 
                          border border-{{ $action['color'] }}-200 dark:border-{{ $action['color'] }}-700/50 
                          hover:border-{{ $action['color'] }}-300 dark:hover:border-{{ $action['color'] }}-600
                          rounded-lg transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5 block">
                    @php $iconComponent = 'heroicon-o-' . $action['icon']; @endphp
                    <x-dynamic-component :component="$iconComponent" class="h-6 w-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400 flex-shrink-0" />
                    <span class="font-medium text-sm text-{{ $action['color'] }}-800 dark:text-{{ $action['color'] }}-200">
                        {{ $action['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate GitHub-style contribution graph
    const contributionData = @json($dashboardData['contribution_data']);
    generateContributionGraph(contributionData);
});

function generateContributionGraph(data) {
    const container = document.getElementById('contributionGraph');
    const now = new Date();
    const oneYearAgo = new Date(now.getFullYear() - 1, now.getMonth(), now.getDate());
    
    // Generate all dates for the past year
    const dates = [];
    for (let d = new Date(oneYearAgo); d <= now; d.setDate(d.getDate() + 1)) {
        dates.push(new Date(d));
    }
    
    // Group dates by week
    const weeks = [];
    let currentWeek = [];
    
    dates.forEach((date, index) => {
        if (index === 0) {
            // Pad the first week with empty cells if needed
            const dayOfWeek = date.getDay();
            for (let i = 0; i < dayOfWeek; i++) {
                currentWeek.push(null);
            }
        }
        
        currentWeek.push(date);
        
        if (currentWeek.length === 7) {
            weeks.push(currentWeek);
            currentWeek = [];
        }
    });
    
    // Add remaining days to last week
    if (currentWeek.length > 0) {
        while (currentWeek.length < 7) {
            currentWeek.push(null);
        }
        weeks.push(currentWeek);
    }
    
    // Create the grid
    const grid = document.createElement('div');
    grid.className = 'contribution-grid';
    
    weeks.forEach(week => {
        const weekColumn = document.createElement('div');
        weekColumn.className = 'contribution-week';
        
        week.forEach(date => {
            const dayCell = document.createElement('div');
            dayCell.className = 'contribution-day';
            
            if (date) {
                const dateStr = date.toISOString().split('T')[0];
                const count = data[dateStr]?.count || 0;
                const level = getContributionLevel(count);
                
                dayCell.classList.add(`contribution-level-${level}`);
                dayCell.title = `${count} tickets resolved on ${date.toLocaleDateString()}`;
                dayCell.setAttribute('data-date', dateStr);
                dayCell.setAttribute('data-count', count);
            } else {
                dayCell.classList.add('contribution-empty');
            }
            
            weekColumn.appendChild(dayCell);
        });
        
        grid.appendChild(weekColumn);
    });
    
    container.appendChild(grid);
}

function getContributionLevel(count) {
    if (count === 0) return 0;
    if (count <= 2) return 1;
    if (count <= 4) return 2;
    if (count <= 6) return 3;
    return 4;
}
</script>
@endpush