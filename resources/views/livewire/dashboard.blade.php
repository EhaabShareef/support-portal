<div class="space-y-6">

    {{-- Page Heading --}}
    <h1 class="text-heading-1">
        Welcome to the Support Dashboard
    </h1>

    {{-- Overview Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @can('tickets.view')
            <div class="card hover-lift">
                <div class="text-body-secondary">Open Tickets</div>
                <div class="text-2xl font-bold text-heading-2">{{ $stats['open'] ?? 0 }}</div>
            </div>
            <div class="card hover-lift">
                <div class="text-body-secondary">Resolved Today</div>
                <div class="text-2xl font-bold text-heading-2">{{ $stats['resolvedToday'] ?? 0 }}</div>
            </div>
        @endcan

        @can('organizations.view')
            <div class="card hover-lift">
                <div class="text-body-secondary">Organizations</div>
                <div class="text-2xl font-bold text-heading-2">{{ $stats['organizations'] ?? 0 }}</div>
            </div>
        @endcan

        @can('users.view')
            <div class="card hover-lift">
                <div class="text-body-secondary">Active Users</div>
                <div class="text-2xl font-bold text-heading-2">{{ $stats['activeUsers'] ?? 0 }}</div>
            </div>
        @endcan
    </div>

    {{-- Ticket Trends Chart --}}
    @can('tickets.view')
        <div class="card h-72">
            <div class="card-header">
                <h2 class="text-heading-4">Ticket Trends</h2>
                <span class="text-body-tertiary">Last 7 days</span>
            </div>
            <div class="p-4 h-full">
                <canvas id="ticket-trends-chart" class="w-full h-full"></canvas>
            </div>
        </div>
    @endcan

    {{-- Quick Actions --}}
    <div class="content-section">
        <h2 class="text-heading-4">Quick Actions</h2>
        <div class="grid-responsive">
            @can('tickets.create')
                <a href="{{ route('tickets.create') }}" class="btn-primary flex items-center">
                    <x-heroicon-o-plus-circle class="w-5 h-5 mr-2" />
                    Create Ticket
                </a>
            @endcan

            @can('organizations.view')
                <a href="{{ route('organizations.index') }}" class="btn-success flex items-center">
                    <x-heroicon-o-building-office class="h-5 w-5 mr-2" />
                    Manage Organizations
                </a>
            @endcan

            @can('users.view')
                <a href="{{ route('users.index') }}" class="btn-secondary flex items-center">
                    <x-heroicon-o-user-group class="h-5 w-5 mr-2" />
                    Manage Users
                </a>
            @endcan
        </div>
    </div>

    {{-- Recent Activity Placeholder --}}
    <div class="content-section">
        <h2 class="text-heading-4 mb-3">Recent Activity</h2>
        <ul class="space-y-2 text-body">
            <li>📝 Ticket #3482 opened by <strong class="text-body">Aysha</strong></li>
            <li>✅ Ticket #3419 resolved by <strong class="text-body">Ibrahim</strong></li>
            <li>👤 New user <strong class="text-body">Ali Rasheed</strong> added to Org #3</li>
            <li>🔧 System maintenance scheduled for tonight</li>
        </ul>
    </div>

</div>

@push('scripts')
    @can('tickets.view')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('livewire:load', () => {
                const ctx = document.getElementById('ticket-trends-chart');
                if (!ctx) return;

                const data = {
                    labels: @json(array_keys($ticketTrends)),
                    datasets: [{
                        label: 'Tickets',
                        data: @json(array_values($ticketTrends)),
                        borderColor: 'rgb(59,130,246)',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        tension: 0.4,
                        fill: true,
                    }],
                };

                new Chart(ctx, {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                        },
                    },
                });
            });
        </script>
    @endcan
@endpush
