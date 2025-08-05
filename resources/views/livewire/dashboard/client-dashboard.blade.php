{{-- Client Dashboard --}}

{{-- Key Metrics Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Total Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    {{ number_format($dashboardData['metrics']['my_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-ticket class="h-8 w-8 text-blue-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                All submitted tickets
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
            <span class="text-red-600 dark:text-red-400 font-medium">
                Requiring attention
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Resolved Tickets</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-600 dark:text-green-400 mt-1">
                    {{ number_format($dashboardData['metrics']['resolved_tickets']) }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-green-600 dark:text-green-400 font-medium">
                Successfully completed
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Avg Response</p>
                <p class="text-2xl sm:text-3xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                    {{ $dashboardData['metrics']['avg_response_time'] }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <x-heroicon-o-clock class="h-8 w-8 text-sky-500" />
            </div>
        </div>
        <div class="mt-3 flex items-center text-sm">
            <span class="text-neutral-600 dark:text-neutral-400">
                Average response time
            </span>
        </div>
    </div>
</div>

{{-- Ticket Status Breakdown and Active Contracts --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Ticket Status Breakdown --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-chart-pie class="h-5 w-5 text-blue-500" />
                Ticket Status Overview
            </h3>
        </div>
        <div class="space-y-4">
            @php
                $statusConfig = [
                    'open' => ['color' => 'red', 'icon' => 'exclamation-circle', 'label' => 'Open'],
                    'in_progress' => ['color' => 'yellow', 'icon' => 'clock', 'label' => 'In Progress'],
                    'awaiting_customer_response' => ['color' => 'blue', 'icon' => 'chat-bubble-left-right', 'label' => 'Awaiting Your Response'],
                    'solution_provided' => ['color' => 'green', 'icon' => 'check', 'label' => 'Solution Provided'],
                    'closed' => ['color' => 'gray', 'icon' => 'check-circle', 'label' => 'Closed'],
                ];
                $totalTickets = array_sum($dashboardData['ticket_breakdown']);
            @endphp

            @if($totalTickets > 0)
                <div class="h-32 relative mb-4">
                    <canvas id="ticketStatusChart"></canvas>
                </div>
            @endif

            @foreach($dashboardData['ticket_breakdown'] as $status => $count)
                @if($count > 0)
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
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $percentage }}%</div>
                            <span class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 min-w-[2rem] text-right">
                                {{ $count }}
                            </span>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Active Contracts --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-document-text class="h-5 w-5 text-green-500" />
                Active Contracts
            </h3>
        </div>
        <div class="space-y-3">
            @if($dashboardData['contracts']->count() > 0)
                @foreach($dashboardData['contracts'] as $contract)
                    <div class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-700/50">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="font-medium text-sm text-green-800 dark:text-green-200">
                                    {{ $contract->contract_number }}
                                </div>
                                <div class="text-xs text-green-600 dark:text-green-400">
                                    {{ $contract->department->name }}
                                </div>
                            </div>
                            <span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 text-xs font-medium px-2 py-1 rounded-full">
                                {{ ucfirst($contract->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-green-700 dark:text-green-300">
                            <span>{{ $contract->start_date->format('M d, Y') }} - {{ $contract->end_date->format('M d, Y') }}</span>
                            @if($contract->includes_hardware)
                                <span class="bg-green-200 dark:bg-green-800 px-2 py-1 rounded">Hardware Included</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                    <x-heroicon-o-document class="h-12 w-12 mx-auto mb-3 opacity-50" />
                    <p class="text-sm">No active contracts found</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Recent Tickets and Quick Actions --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
    {{-- Recent Tickets --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                <x-heroicon-o-clock class="h-5 w-5 text-gray-500" />
                Recent Tickets
            </h3>
            <a href="{{ route('tickets.index') }}" class="text-sm text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300">
                View All
            </a>
        </div>
        <div class="space-y-3 max-h-64 overflow-y-auto">
            @if($dashboardData['recent_tickets']->count() > 0)
                @foreach($dashboardData['recent_tickets'] as $ticket)
                    <a href="{{ route('tickets.show', $ticket) }}" class="block p-3 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ $ticket->ticket_number }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full 
                                    {{ $ticket->status === 'open' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 
                                       ($ticket->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 
                                        ($ticket->status === 'closed' ? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $ticket->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="text-sm text-neutral-700 dark:text-neutral-300 truncate mb-1">
                            {{ $ticket->subject }}
                        </div>
                        <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                            <span>{{ $ticket->department->name }}</span>
                            @if($ticket->assigned)
                                <span>Assigned to {{ $ticket->assigned->name }}</span>
                            @else
                                <span>Unassigned</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            @else
                <div class="text-center py-8 text-neutral-500 dark:text-neutral-400">
                    <x-heroicon-o-ticket class="h-12 w-12 mx-auto mb-3 opacity-50" />
                    <p class="text-sm">No tickets found</p>
                </div>
            @endif
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
                    <div>
                        <div class="font-medium text-sm text-{{ $action['color'] }}-800 dark:text-{{ $action['color'] }}-200">
                            {{ $action['label'] }}
                        </div>
                        @if($action['label'] === 'Create Ticket')
                            <div class="text-xs text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400">
                                Get help with your issues
                            </div>
                        @else
                            <div class="text-xs text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400">
                                View and manage your tickets
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach

            {{-- Contact Support Card --}}
            <div class="p-4 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg border border-indigo-200 dark:border-indigo-700/50">
                <div class="flex items-center gap-3 mb-2">
                    <x-heroicon-o-phone class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <span class="font-medium text-sm text-indigo-800 dark:text-indigo-200">Need Immediate Help?</span>
                </div>
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-2">
                    For urgent matters, contact our support team directly
                </p>
                <div class="flex items-center gap-4 text-xs">
                    <span class="text-indigo-700 dark:text-indigo-300 font-medium">📞 +960 123-4567</span>
                    <span class="text-indigo-700 dark:text-indigo-300 font-medium">✉️ support@ht.com</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ticket Status Pie Chart
    const statusCtx = document.getElementById('ticketStatusChart');
    if (statusCtx) {
        const statusData = @json($dashboardData['ticket_breakdown']);
        const labels = [];
        const data = [];
        const colors = [];
        
        const colorMap = {
            'open': '#dc2626',
            'in_progress': '#eab308',
            'awaiting_customer_response': '#3b82f6',
            'solution_provided': '#16a34a',
            'closed': '#6b7280'
        };
        
        Object.entries(statusData).forEach(([status, count]) => {
            if (count > 0) {
                labels.push(status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                data.push(count);
                colors.push(colorMap[status] || '#6b7280');
            }
        });
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#ffffff'
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
                cutout: '60%'
            }
        });
    }
});
</script>
@endpush