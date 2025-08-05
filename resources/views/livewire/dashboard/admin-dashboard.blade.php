{{-- Admin Dashboard --}}

{{-- Key Metrics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                    {{ number_format($dashboardData['metrics']['total_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-ticket class="h-8 w-8 text-blue-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">
                {{ $dashboardData['metrics']['resolved_today'] }} resolved today
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Open Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-red-600 dark:text-red-400 mt-1">
                    {{ number_format($dashboardData['metrics']['open_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-red-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                Requiring attention
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Organizations</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                    {{ number_format($dashboardData['metrics']['organizations']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-building-office-2 class="h-8 w-8 text-green-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                Active clients
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Active Users</p>
                <p class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                    {{ number_format($dashboardData['metrics']['active_users']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-users class="h-8 w-8 text-sky-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                System users
            </span>
        </div>
    </div>
</div>

{{-- Charts and Analytics Row --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Ticket Trends Chart --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-500" />
                Ticket Trends
            </h3>
            <span class="text-sm text-neutral-500 dark:text-neutral-400">Last 30 days</span>
        </div>
        <div class="h-64">
            <canvas id="ticketTrendsChart"></canvas>
        </div>
    </div>

    {{-- Department Breakdown --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-building-office class="h-5 w-5 text-green-500" />
                Department Activity
            </h3>
            <span class="text-sm text-neutral-500 dark:text-neutral-400">This week</span>
        </div>
        <div class="space-y-3">
            @foreach($dashboardData['department_breakdown'] as $department)
                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-blue-500"></div>
                        <span class="font-medium text-sm text-neutral-800 dark:text-neutral-200">{{ $department->name }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-neutral-600 dark:text-neutral-400">
                            {{ $department->open_tickets_count }} open
                        </span>
                        <span class="font-semibold text-green-600 dark:text-green-400">
                            {{ $department->resolved_this_week_count }} resolved
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Notifications and Alerts --}}
@if($dashboardData['contract_alerts']->count() > 0 || $dashboardData['hardware_alerts']->count() > 0)
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Contract Alerts --}}
    @if($dashboardData['contract_alerts']->count() > 0)
    <div class="dashboard-card border-l-4 border-amber-500">
        <div class="flex items-center gap-3 mb-4">
            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-amber-500" />
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                Contracts Expiring Soon
            </h3>
            <span class="bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200 text-xs font-medium px-2 py-1 rounded-full">
                {{ $dashboardData['contract_alerts']->count() }}
            </span>
        </div>
        <div class="space-y-3">
            @foreach($dashboardData['contract_alerts']->take(3) as $contract)
                <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                    <div>
                        <div class="font-medium text-sm text-neutral-800 dark:text-neutral-200">
                            {{ $contract->organization->name }}
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                            {{ $contract->department->name }} • {{ $contract->contract_number }}
                        </div>
                    </div>
                    <div class="text-xs text-amber-700 dark:text-amber-300 font-medium">
                        {{ $contract->end_date->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hardware Alerts --}}
    @if($dashboardData['hardware_alerts']->count() > 0)
    <div class="dashboard-card border-l-4 border-red-500">
        <div class="flex items-center gap-3 mb-4">
            <x-heroicon-o-server class="h-6 w-6 text-red-500" />
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                Hardware Attention Required
            </h3>
            <span class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 text-xs font-medium px-2 py-1 rounded-full">
                {{ $dashboardData['hardware_alerts']->count() }}
            </span>
        </div>
        <div class="space-y-3">
            @foreach($dashboardData['hardware_alerts']->take(3) as $hardware)
                <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                    <div>
                        <div class="font-medium text-sm text-neutral-800 dark:text-neutral-200">
                            {{ $hardware->organization->name }}
                        </div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                            {{ $hardware->hardware_type }} • {{ $hardware->serial_number }}
                        </div>
                    </div>
                    <div class="text-xs text-red-700 dark:text-red-300 font-medium">
                        @if($hardware->warranty_expiration && $hardware->warranty_expiration <= now()->addDays(30))
                            Warranty expires {{ $hardware->warranty_expiration->diffForHumans() }}
                        @else
                            Maintenance due {{ $hardware->next_maintenance->diffForHumans() }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

{{-- Top Performers and Quick Actions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Top Performers --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-trophy class="h-5 w-5 text-yellow-500" />
                Top Performers This Week
            </h3>
        </div>
        <div class="space-y-4">
            @foreach($dashboardData['top_performers'] as $department)
                @if($department->users->count() > 0)
                <div>
                    <h4 class="font-medium text-sm text-neutral-700 dark:text-neutral-300 mb-2">{{ $department->name }}</h4>
                    <div class="space-y-2">
                        @foreach($department->users as $index => $user)
                            <div class="flex items-center justify-between p-2 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-800' : ($index === 1 ? 'bg-gray-100 text-gray-800' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">{{ $user->name }}</span>
                                </div>
                                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ $user->resolved_count }} resolved
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
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
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($dashboardData['quick_actions'] as $action)
                <a href="{{ route($action['route']) }}" 
                   class="flex items-center gap-3 p-4 bg-gradient-to-r from-{{ $action['color'] }}-50 to-{{ $action['color'] }}-100 
                          dark:from-{{ $action['color'] }}-900/20 dark:to-{{ $action['color'] }}-800/20 
                          border border-{{ $action['color'] }}-200 dark:border-{{ $action['color'] }}-700/50 
                          hover:border-{{ $action['color'] }}-300 dark:hover:border-{{ $action['color'] }}-600
                          rounded-lg transition-all duration-200 hover:shadow-md transform hover:-translate-y-0.5">
                    @php
                        $iconComponent = 'heroicon-o-' . $action['icon'];
                    @endphp
                    <x-dynamic-component :component="$iconComponent" class="h-6 w-6 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400" />
                    <span class="font-medium text-sm text-{{ $action['color'] }}-800 dark:text-{{ $action['color'] }}-200">
                        {{ $action['label'] }}
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ticket Trends Chart
    const trendsCtx = document.getElementById('ticketTrendsChart').getContext('2d');
    const trendsData = @json($dashboardData['ticket_trends']);
    
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: trendsData.map(item => new Date(item.date).toLocaleDateString()),
            datasets: [{
                label: 'Tickets Created',
                data: trendsData.map(item => item.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endpush