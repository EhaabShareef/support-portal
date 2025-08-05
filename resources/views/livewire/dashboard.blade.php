<div class="space-y-6">

    {{-- Page Heading --}}
    <h1 class="text-heading-1">
        Welcome to the Support Dashboard
    </h1>

    {{-- Overview Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card">
            <div class="text-body-secondary">Open Tickets</div>
            <div class="text-2xl font-bold text-heading-2">24</div>
        </div>
        <div class="card">
            <div class="text-body-secondary">Resolved Today</div>
            <div class="text-2xl font-bold text-heading-2">8</div>
        </div>
        <div class="card">
            <div class="text-body-secondary">Organizations</div>
            <div class="text-2xl font-bold text-heading-2">53</div>
        </div>
        <div class="card">
            <div class="text-body-secondary">Users Online</div>
            <div class="text-2xl font-bold text-heading-2">5</div>
        </div>
    </div>

    {{-- Chart Placeholder --}}
    <div class="card h-64">
        <div class="card-header">
            <h2 class="text-heading-4">Ticket Trends</h2>
            <span class="text-body-tertiary">Last 7 days</span>
        </div>
        <div class="w-full h-full flex-center text-body-secondary">
            [Chart Placeholder]
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="content-section">
        <h2 class="text-heading-4">Quick Actions</h2>
        <div class="grid-responsive">
            <a href="{{ route('tickets.create') }}" class="btn-primary">
                <x-heroicon-o-plus-circle class="w-5 h-5 mr-2" />
                Create Ticket
            </a>

            <a href="#" class="btn-success">
                <x-heroicon-o-building-office class="h-5 w-5 mr-2" />
                Manage Organizations
            </a>
            
            <a href="#" class="btn-secondary">
                <x-heroicon-o-server class="h-5 w-5 mr-2" />
                Hardware Inventory
            </a>
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
