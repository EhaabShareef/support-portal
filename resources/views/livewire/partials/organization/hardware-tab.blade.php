<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Hardware</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Overview of hardware assets for this organization</p>
        </div>
        
        @if($organization->hardware->count() > 0)
            <div class="flex gap-2">
                <a href="{{ route('hardware.manage', $organization) }}" 
                   class="inline-flex items-center px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                    <x-heroicon-o-list-bullet class="h-4 w-4 mr-2" />
                    View All
                </a>
                <a href="{{ route('organizations.hardware.create', $organization) }}" 
                   class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Hardware
                </a>
            </div>
        @endif
    </div>

    {{-- Hardware List --}}
    @if($organization->hardware->count() > 0)
        <div class="space-y-2">
            @foreach($organization->hardware()->with(['type', 'contract', 'serials'])->latest('purchase_date')->take(5)->get() as $hardware)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-3 hover:bg-white/10 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                        {{ $hardware->asset_tag ?: ($hardware->brand . ' ' . $hardware->model) }}
                                    </h4>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 flex-shrink-0">
                                        Active
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-cpu-chip class="h-3 w-3" />
                                        {{ $hardware->type?->name ?? ucfirst($hardware->hardware_type) }}
                                    </span>
                                    
                                    @if($hardware->brand && $hardware->model)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-tag class="h-3 w-3" />
                                        {{ $hardware->brand }} {{ $hardware->model }}
                                    </span>
                                    @endif
                                    
                                    @if($hardware->quantity)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-numbered-list class="h-3 w-3" />
                                        Qty: {{ $hardware->quantity }}
                                    </span>
                                    @endif
                                    
                                    {{-- Serial Status --}}
                                    @if($hardware->serial_number)
                                    <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                        <x-heroicon-o-hashtag class="h-3 w-3" />
                                        {{ $hardware->serial_number }}
                                    </span>
                                    @elseif($hardware->serial_required && $hardware->serials && $hardware->serials->count() > 0)
                                    <span class="flex items-center gap-1 {{ $hardware->serials->count() == $hardware->quantity ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                        <x-heroicon-o-hashtag class="h-3 w-3" />
                                        Serials: {{ $hardware->serials->count() }}/{{ $hardware->quantity }}
                                    </span>
                                    @elseif($hardware->serial_required)
                                    <span class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                        <x-heroicon-o-exclamation-triangle class="h-3 w-3" />
                                        Serials Missing
                                    </span>
                                    @endif
                                    
                                    @if($hardware->contract)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-document-text class="h-3 w-3" />
                                        {{ $hardware->contract->contract_number }}
                                    </span>
                                    @endif
                                    
                                    @if($hardware->purchase_date)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        {{ $hardware->purchase_date->format('M Y') }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- View All Link --}}
        <div class="text-center pt-4 flex gap-4 justify-center">
            <a href="{{ route('hardware.manage', $organization) }}" 
               class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 font-medium">
                <x-heroicon-o-list-bullet class="h-4 w-4 mr-1" />
                View All Hardware ({{ $organization->hardware->count() }})
            </a>
            <a href="{{ route('organizations.hardware.create', $organization) }}" 
               class="inline-flex items-center text-sm text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 font-medium">
                <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                Add More Hardware
            </a>
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any hardware assets yet.</p>
            @can('hardware.create')
            <div class="mt-6">
                <a href="{{ route('organizations.hardware.create', $organization) }}" 
                   class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First Hardware
                </a>
            </div>
            @endcan
        </div>
    @endif
</div>