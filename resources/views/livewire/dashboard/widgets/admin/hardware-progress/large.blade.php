<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <x-heroicon-o-cpu-chip class="h-6 w-6 text-sky-500" />
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">Hardware Progress</h3>
        </div>
        <div class="flex items-center gap-2">
            @if($data['total_tickets'] > 0)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-sky-100 dark:bg-sky-900/30 text-sky-800 dark:text-sky-200">
                    {{ $data['total_tickets'] }} {{ Str::plural('ticket', $data['total_tickets']) }}
                </span>
            @endif
            <button wire:click="refreshData" 
                    class="p-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
                <x-heroicon-o-arrow-path class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-48 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-12 w-12 mx-auto mb-3" />
                <p class="text-lg">Failed to load data</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-6" aria-busy="true" role="status" aria-label="Loading hardware progress">
            <div class="text-center">
                <div class="animate-pulse h-16 bg-neutral-200 dark:bg-neutral-700 rounded w-24 mx-auto mb-3"></div>
                <div class="animate-pulse h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-32 mx-auto mb-6"></div>
                <div class="animate-pulse h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-full mb-6"></div>
            </div>
            <div class="grid grid-cols-3 gap-6">
                <div class="animate-pulse h-16 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-16 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-16 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
            </div>
            <div class="space-y-3">
                <div class="animate-pulse h-4 bg-neutral-200 dark:bg-neutral-700 rounded w-32"></div>
                <div class="animate-pulse h-12 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-12 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-12 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
            </div>
        </div>
    @else
        @if($data['total_units'] > 0)
            {{-- Large Progress Display --}}
            <div class="space-y-6">
                {{-- Overall Progress Section --}}
                <div class="text-center">
                    <div class="text-4xl font-bold text-sky-600 dark:text-sky-400 mb-2">
                        {{ $data['percentage'] }}%
                    </div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                        Overall Hardware Progress
                    </div>
                    
                    {{-- Large Progress Bar --}}
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-4 mb-6">
                        <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-4 rounded-full transition-all duration-500" 
                             style="width: {{ $data['percentage'] }}%"></div>
                    </div>
                </div>
                
                {{-- Overall Stats --}}
                <div class="grid grid-cols-3 gap-6 mb-6">
                    <div class="text-center p-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">{{ $data['total_units'] }}</div>
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">Total Units</div>
                    </div>
                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $data['fixed_units'] }}</div>
                        <div class="text-sm text-green-600 dark:text-green-400">Fixed</div>
                    </div>
                    <div class="text-center p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                        <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $data['pending_units'] }}</div>
                        <div class="text-sm text-amber-600 dark:text-amber-400">Pending</div>
                    </div>
                </div>

                {{-- Organization Breakdown --}}
                @if($organizationBreakdown->isNotEmpty())
                    <div>
                        <h4 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
                            Organization Breakdown
                        </h4>
                        <div class="space-y-3 max-h-48 overflow-y-auto custom-scrollbar scrollbar-on-hover">
                            @foreach($organizationBreakdown->take(5) as $org)
                                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/30 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                            {{ $org['name'] }}
                                        </div>
                                        <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                            {{ $org['total_tickets'] }} {{ Str::plural('ticket', $org['total_tickets']) }}
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
                                                {{ $org['percentage'] }}%
                                            </div>
                                            <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                                {{ $org['fixed_units'] }}/{{ $org['total_units'] }}
                                            </div>
                                        </div>
                                        <div class="w-16 bg-neutral-200 dark:bg-neutral-700 rounded-full h-2">
                                            <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-2 rounded-full transition-all duration-300" 
                                                 style="width: {{ $org['percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @else
            {{-- No Data State --}}
            <div class="text-center py-12">
                <x-heroicon-o-cpu-chip class="mx-auto h-16 w-16 text-neutral-400" />
                <h3 class="mt-3 text-lg font-medium text-neutral-900 dark:text-neutral-100">
                    No Hardware Tickets
                </h3>
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">
                    No active hardware tickets found.
                </p>
            </div>
        @endif
    @endif
</div>
