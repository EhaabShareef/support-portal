<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Hardware</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Manage hardware assets for this organization</p>
        </div>
        
        @can('hardware.create')
        <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            Add Hardware
        </button>
        @endcan
    </div>

    {{-- Hardware List --}}
    @if($organization->hardware->count() > 0)
        <div class="space-y-3">
            @foreach($organization->hardware as $hardware)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 hover:bg-white/10 transition-all duration-200">
                    <div class="flex flex-col gap-4">
                        {{-- Hardware Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-base font-medium text-neutral-800 dark:text-neutral-100">
                                        {{ $hardware->brand }} {{ $hardware->model }}
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($hardware->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($hardware->status === 'maintenance') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @elseif($hardware->status === 'retired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @endif">
                                        {{ ucfirst($hardware->status) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-cpu-chip class="h-3 w-3" />
                                        {{ ucfirst($hardware->hardware_type) }}
                                    </span>
                                    @if($hardware->asset_tag)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-tag class="h-3 w-3" />
                                        {{ $hardware->asset_tag }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @can('hardware.view')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                                    <x-heroicon-o-eye class="h-3 w-3 mr-1" />
                                    View
                                </button>
                                @endcan
                                
                                @can('hardware.edit')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                    <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                                    Edit
                                </button>
                                @endcan
                                
                                @can('hardware.delete')
                                <button class="inline-flex items-center px-3 py-1.5 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200">
                                    <x-heroicon-o-trash class="h-3 w-3 mr-1" />
                                    Delete
                                </button>
                                @endcan
                            </div>
                        </div>

                        {{-- Hardware Details Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-xs">
                            @if($hardware->serial_number)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-identification class="h-3 w-3" />
                                <span class="font-medium">Serial:</span>
                                <span class="font-mono">{{ $hardware->serial_number }}</span>
                            </div>
                            @endif
                            @if($hardware->purchase_date)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-calendar class="h-3 w-3" />
                                <span class="font-medium">Purchased:</span>
                                <span>{{ $hardware->purchase_date->format('M d, Y') }}</span>
                            </div>
                            @endif
                            @if($hardware->purchase_price)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-currency-dollar class="h-3 w-3" />
                                <span class="font-medium">Price:</span>
                                <span>${{ number_format($hardware->purchase_price, 2) }}</span>
                            </div>
                            @endif
                            @if($hardware->warranty_expiration)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-shield-check class="h-3 w-3" />
                                <span class="font-medium">Warranty:</span>
                                <span class="{{ $hardware->warranty_expiration->isPast() ? 'text-red-500' : 'text-green-500' }}">
                                    {{ $hardware->warranty_expiration->format('M d, Y') }}
                                </span>
                            </div>
                            @endif
                            @if($hardware->location)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-map-pin class="h-3 w-3" />
                                <span class="font-medium">Location:</span>
                                <span>{{ $hardware->location }}</span>
                            </div>
                            @endif
                            @if($hardware->contract)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-document-text class="h-3 w-3" />
                                <span class="font-medium">Contract:</span>
                                <span>{{ $hardware->contract->contract_name }}</span>
                            </div>
                            @endif
                        </div>

                        {{-- Specifications --}}
                        @if($hardware->specifications)
                        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-cog-6-tooth class="h-3 w-3 mt-0.5 text-neutral-500" />
                                <div>
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Specifications</span>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        {{ Str::limit($hardware->specifications, 150) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Maintenance Info --}}
                        @if($hardware->last_maintenance || $hardware->next_maintenance)
                        <div class="flex items-center gap-4 text-xs text-neutral-500 dark:text-neutral-400 border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            @if($hardware->last_maintenance)
                            <span class="flex items-center gap-1">
                                <x-heroicon-o-wrench-screwdriver class="h-3 w-3" />
                                Last: {{ $hardware->last_maintenance->format('M d, Y') }}
                            </span>
                            @endif
                            @if($hardware->next_maintenance)
                            <span class="flex items-center gap-1 {{ $hardware->next_maintenance->isPast() ? 'text-red-500' : 'text-yellow-500' }}">
                                <x-heroicon-o-clock class="h-3 w-3" />
                                Next: {{ $hardware->next_maintenance->format('M d, Y') }}
                            </span>
                            @endif
                        </div>
                        @endif

                        {{-- Remarks --}}
                        @if($hardware->remarks)
                        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="h-3 w-3 mt-0.5 text-neutral-500" />
                                <div>
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Remarks</span>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        {{ $hardware->remarks }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any hardware assets yet.</p>
            @can('hardware.create')
            <div class="mt-6">
                <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First Hardware
                </button>
            </div>
            @endcan
        </div>
    @endif
</div>