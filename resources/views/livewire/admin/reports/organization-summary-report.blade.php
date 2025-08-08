<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">Organization Summary</h1>
                <p class="mt-2 text-neutral-600 dark:text-neutral-400">
                    High-level overview of client engagement and health
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

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-heroicon-o-building-office-2 class="h-8 w-8 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Organizations</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($totalStats['total_organizations']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-heroicon-o-users class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Active Users</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($totalStats['total_users']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-heroicon-o-document-text class="h-8 w-8 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Active Contracts</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($totalStats['total_contracts']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-heroicon-o-computer-desktop class="h-8 w-8 text-orange-600 dark:text-orange-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Hardware Assets</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($totalStats['total_hardware']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <x-heroicon-o-ticket class="h-8 w-8 text-red-600 dark:text-red-400" />
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-neutral-600 dark:text-neutral-400">Open Tickets</p>
                    <p class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ number_format($totalStats['total_open_tickets']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 mb-8">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">Filters</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Search Organizations
                    </label>
                    <input type="text" wire:model.live="search" id="search" placeholder="Search by name..."
                           class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                </div>

                <!-- Subscription Status -->
                <div>
                    <label for="subscriptionStatus" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Subscription Status
                    </label>
                    <select wire:model.live="subscriptionStatus" id="subscriptionStatus" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All Statuses</option>
                        @foreach($subscriptionStatuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Active Status -->
                <div>
                    <label for="isActive" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Active Status
                    </label>
                    <select wire:model.live="isActive" id="isActive" 
                            class="block w-full rounded-lg border border-neutral-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 px-3 py-2 text-sm text-neutral-900 dark:text-neutral-100 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-blue-500 dark:focus:ring-blue-400">
                        <option value="">All</option>
                        @foreach($activeStatuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end">
                <button wire:click="resetFilters" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Organizations Table -->
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                Organization Details
            </h3>
            
            @if($organizations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                        <thead class="bg-neutral-50 dark:bg-neutral-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Organization
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Active Users
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Contracts
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Hardware
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Open Tickets
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">
                                    Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                            @foreach($organizations as $org)
                                <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-2 w-2 rounded-full {{ $org->is_active ? 'bg-green-400' : 'bg-red-400' }} mr-3"></div>
                                            <div>
                                                <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                    {{ $org->name }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $org->subscription_status === 'Active' ? 'bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-400' : '' }}
                                            {{ $org->subscription_status === 'Trial' ? 'bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-400' : '' }}
                                            {{ $org->subscription_status === 'Suspended' ? 'bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400' : '' }}
                                            {{ $org->subscription_status === 'Cancelled' ? 'bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400' : '' }}
                                        ">
                                            {{ $org->subscription_status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ number_format($org->active_users_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ number_format($org->active_contracts_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ number_format($org->hardware_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ number_format($org->open_tickets_count) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-600 dark:text-neutral-400">
                                        {{ $org->created_at->format('M j, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $organizations->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-building-office-2 class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No organizations found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        Try adjusting your filters to see organization data.
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