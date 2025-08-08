<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">Ticket Volume & Status Trends</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                    Track ticket counts and workload trends by status over time
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button wire:click="export" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 mr-2" />
                    Export
                </button>
                <a href="{{ route('admin.reports.dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-2" />
                    Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 mb-8">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Filters</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Date Range -->
                <div>
                    <label for="startDate" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Start Date
                    </label>
                    <input type="date" wire:model.live="startDate" id="startDate" 
                           class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                </div>
                
                <div>
                    <label for="endDate" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        End Date
                    </label>
                    <input type="date" wire:model.live="endDate" id="endDate" 
                           class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                </div>

                <!-- Organization -->
                <div>
                    <label for="organization" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Organization
                    </label>
                    <select wire:model.live="organizationId" id="organization" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Organizations</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Department Group -->
                <div>
                    <label for="departmentGroup" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Department Group
                    </label>
                    <select wire:model.live="departmentGroupId" id="departmentGroup" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Department Groups</option>
                        @foreach($departmentGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Department -->
                <div>
                    <label for="department" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Department
                    </label>
                    <select wire:model.live="departmentId" id="department" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned Agent -->
                <div>
                    <label for="agent" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Assigned Agent
                    </label>
                    <select wire:model.live="assignedUserId" id="agent" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Agents</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Ticket Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Ticket Type
                    </label>
                    <select wire:model.live="ticketType" id="type" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Types</option>
                        @foreach($ticketTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Priority
                    </label>
                    <select wire:model.live="priority" id="priority" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Priorities</option>
                        @foreach($priorities as $prio)
                            <option value="{{ $prio }}">{{ $prio }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Status
                    </label>
                    <select wire:model.live="status" id="status" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $stat)
                            <option value="{{ $stat }}">{{ $stat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Group By -->
                <div>
                    <label for="groupBy" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Group By
                    </label>
                    <select wire:model.live="groupBy" id="groupBy" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="status">Status</option>
                        <option value="priority">Priority</option>
                        <option value="type">Type</option>
                        <option value="date">Date</option>
                    </select>
                </div>

                <!-- Date Grouping (only show when groupBy is date) -->
                @if($groupBy === 'date')
                <div>
                    <label for="dateGrouping" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Date Grouping
                    </label>
                    <select wire:model.live="dateGrouping" id="dateGrouping" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                @endif
            </div>

            <div class="flex items-center justify-between">
                <button wire:click="resetFilters" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset Filters
                </button>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Total Tickets: <span class="font-semibold">{{ number_format($totalTickets) }}</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                Ticket Volume by {{ ucfirst($groupBy) }}
            </h3>
            
            @if($ticketData && $ticketData->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    {{ ucfirst($groupBy) }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Count
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Percentage
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Visual
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach($ticketData as $row)
                                @php
                                    $percentage = $totalTickets > 0 ? ($row->count / $totalTickets) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                        {{ $groupBy === 'date' ? $row->period : $row->{$groupBy} }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ number_format($row->count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ number_format($percentage, 1) }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-chart-bar class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No data available</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Try adjusting your filters to see ticket volume data.
                    </p>
                </div>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex">
                <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400" />
                <div class="ml-3">
                    <p class="text-sm text-blue-800 dark:text-blue-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>