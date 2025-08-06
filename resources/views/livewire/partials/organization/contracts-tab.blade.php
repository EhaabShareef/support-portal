<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Contracts</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Overview of active contracts for this organization</p>
        </div>
        
        @if($organization->contracts->count() > 0)
            <a href="{{ route('contracts.manage', $organization) }}" 
               class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-2" />
                Manage Contracts
            </a>
        @endif
    </div>

    {{-- Contracts List --}}
    @if($organization->contracts->count() > 0)
        <div class="space-y-2">
            @foreach($organization->contracts as $contract)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-3 hover:bg-white/10 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                        {{ $contract->contract_number }}
                                    </h4>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium flex-shrink-0
                                        @if($contract->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($contract->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @elseif($contract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($contract->status) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-tag class="h-3 w-3" />
                                        {{ ucfirst($contract->type) }}
                                    </span>
                                    @if($contract->contract_value)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-currency-dollar class="h-3 w-3" />
                                        {{ $contract->currency }} {{ number_format($contract->contract_value, 0) }}
                                    </span>
                                    @endif
                                    @if($contract->end_date)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        Expires {{ $contract->end_date->format('M Y') }}
                                    </span>
                                    @endif
                                    @if($contract->includes_hardware)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-cpu-chip class="h-3 w-3" />
                                        Hardware
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 ml-3 flex-shrink-0">
                            @can('contracts.view')
                            <button class="inline-flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded transition-colors duration-200">
                                <x-heroicon-o-eye class="h-3 w-3" />
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- View All Link --}}
        <div class="text-center pt-4">
            <a href="{{ route('contracts.manage', $organization) }}" 
               class="inline-flex items-center text-sm text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 font-medium">
                <x-heroicon-o-arrow-right class="h-4 w-4 mr-1" />
                View All Contracts ({{ $organization->contracts->count() }})
            </a>
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No contracts found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any contracts yet.</p>
            @can('contracts.create')
            <div class="mt-6">
                <a href="{{ route('contracts.manage', $organization) }}" 
                   class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First Contract
                </a>
            </div>
            @endcan
        </div>
    @endif
</div>