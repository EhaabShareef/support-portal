<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Contracts</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Manage contracts for this organization</p>
        </div>
        
        @can('contracts.create')
        <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            Add Contract
        </button>
        @endcan
    </div>

    {{-- Contracts List --}}
    @if($organization->contracts->count() > 0)
        <div class="space-y-3">
            @foreach($organization->contracts as $contract)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 hover:bg-white/10 transition-all duration-200">
                    <div class="flex flex-col gap-4">
                        {{-- Contract Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-base font-medium text-neutral-800 dark:text-neutral-100">
                                        {{ $contract->contract_name }}
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($contract->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($contract->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @elseif($contract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-2">
                                    {{ $contract->contract_type }} Contract
                                </p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @can('contracts.view')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                                    <x-heroicon-o-eye class="h-3 w-3 mr-1" />
                                    View
                                </button>
                                @endcan
                                
                                @can('contracts.edit')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                    <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                                    Edit
                                </button>
                                @endcan
                                
                                @can('contracts.delete')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200">
                                    <x-heroicon-o-trash class="h-3 w-3 mr-1" />
                                    Delete
                                </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Contract Details Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-calendar class="h-3 w-3" />
                                <span class="font-medium">Start:</span>
                                <span>{{ $contract->start_date ? $contract->start_date->format('M d, Y') : 'Not set' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-calendar-days class="h-3 w-3" />
                                <span class="font-medium">End:</span>
                                <span>{{ $contract->end_date ? $contract->end_date->format('M d, Y') : 'Not set' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-currency-dollar class="h-3 w-3" />
                                <span class="font-medium">Value:</span>
                                <span>{{ $contract->contract_value ? '$' . number_format($contract->contract_value, 2) : 'Not set' }}</span>
                            </div>
                            @if($contract->renewal_date)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-arrow-path class="h-3 w-3" />
                                <span class="font-medium">Renewal:</span>
                                <span>{{ $contract->renewal_date->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($contract->payment_terms)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-credit-card class="h-3 w-3" />
                                <span class="font-medium">Terms:</span>
                                <span>{{ $contract->payment_terms }}</span>
                            </div>
                            @endif
                            @if($contract->contract_manager)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-user class="h-3 w-3" />
                                <span class="font-medium">Manager:</span>
                                <span>{{ $contract->contract_manager }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Contract Description --}}
                        @if($contract->description)
                        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ Str::limit($contract->description, 150) }}
                            </p>
                        </div>
                        @endif

                        {{-- Hardware Count --}}
                        @if($contract->hardware()->count() > 0)
                        <div class="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                            <x-heroicon-o-cpu-chip class="h-3 w-3" />
                            <span>{{ $contract->hardware()->count() }} hardware item{{ $contract->hardware()->count() !== 1 ? 's' : '' }} assigned</span>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No contracts found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any contracts yet.</p>
            @can('contracts.create')
            <div class="mt-6">
                <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First Contract
                </button>
            </div>
            @endcan
        </div>
    @endif
</div>