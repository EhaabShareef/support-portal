<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-cpu-chip class="h-5 w-5 text-sky-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Hardware Progress</h3>
        </div>
        <div class="flex items-center gap-2">
            @if($data['total_tickets'] > 0)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 dark:bg-sky-900/30 text-sky-800 dark:text-sky-200">
                    {{ $data['total_tickets'] }} {{ Str::plural('ticket', $data['total_tickets']) }}
                </span>
            @endif
            <button wire:click="refreshData" 
                    class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
                <x-heroicon-o-arrow-path class="h-3 w-3" />
            </button>
        </div>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-24 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-8 w-8 mx-auto mb-2" />
                <p class="text-sm">Failed to load data</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-4" aria-busy="true" role="status" aria-label="Loading hardware progress">
            <div class="text-center">
                <div class="animate-pulse h-10 bg-neutral-200 dark:bg-neutral-700 rounded w-20 mx-auto mb-2"></div>
                <div class="animate-pulse h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-24 mx-auto mb-4"></div>
                <div class="animate-pulse h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-full mb-4"></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="animate-pulse h-8 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-8 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-8 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
            </div>
        </div>
    @else
        @if($data['total_units'] > 0)
            {{-- Progress Display --}}
            <div class="space-y-4">
                {{-- Main Progress Section --}}
                <div class="text-center">
                    <div class="text-3xl font-bold text-sky-600 dark:text-sky-400 mb-1">
                        {{ $data['percentage'] }}%
                    </div>
                    <div class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                        Overall Progress
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-3 mb-4">
                        <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-3 rounded-full transition-all duration-500" 
                             style="width: {{ $data['percentage'] }}%"></div>
                    </div>
                </div>
                
                {{-- Stats Grid --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-lg font-bold text-neutral-900 dark:text-neutral-100">{{ $data['total_units'] }}</div>
                        <div class="text-xs text-neutral-600 dark:text-neutral-400">Total Units</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-green-600 dark:text-green-400">{{ $data['fixed_units'] }}</div>
                        <div class="text-xs text-green-600 dark:text-green-400">Fixed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $data['pending_units'] }}</div>
                        <div class="text-xs text-amber-600 dark:text-amber-400">Pending</div>
                    </div>
                </div>
            </div>
        @else
            {{-- No Data State --}}
            <div class="text-center py-8">
                <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                    No Hardware Tickets
                </h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    No active hardware tickets found.
                </p>
            </div>
        @endif
    @endif
</div>
