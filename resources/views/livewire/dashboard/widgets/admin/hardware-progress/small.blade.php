<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <x-heroicon-o-cpu-chip class="h-4 w-4 text-sky-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Hardware Progress</h3>
        </div>
        <button wire:click="refreshData" 
                class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
            <x-heroicon-o-arrow-path class="h-3 w-3" />
        </button>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-16 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-6 w-6 mx-auto mb-1" />
                <p class="text-xs">Failed to load</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-2" aria-busy="true" role="status" aria-label="Loading hardware progress">
            <div class="animate-pulse h-8 bg-neutral-200 dark:bg-neutral-700 rounded w-16 mx-auto"></div>
            <div class="animate-pulse h-2 bg-neutral-200 dark:bg-neutral-700 rounded w-full"></div>
            <div class="grid grid-cols-2 gap-2">
                <div class="animate-pulse h-6 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
                <div class="animate-pulse h-6 bg-neutral-200 dark:bg-neutral-700 rounded"></div>
            </div>
        </div>
    @else
        @if($data['total_units'] > 0)
            {{-- Compact Progress Display --}}
            <div class="text-center">
                <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mb-1">
                    {{ $data['percentage'] }}%
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400 mb-3">
                    Complete
                </div>
                
                {{-- Mini Progress Bar --}}
                <div class="w-full bg-neutral-200 dark:bg-neutral-700 rounded-full h-2 mb-3">
                    <div class="bg-gradient-to-r from-sky-500 to-blue-600 h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $data['percentage'] }}%"></div>
                </div>
                
                {{-- Mini Stats --}}
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="text-green-600 dark:text-green-400">
                        <div class="font-semibold">{{ $data['fixed_units'] }}</div>
                        <div>Fixed</div>
                    </div>
                    <div class="text-amber-600 dark:text-amber-400">
                        <div class="font-semibold">{{ $data['pending_units'] }}</div>
                        <div>Pending</div>
                    </div>
                </div>
            </div>
        @else
            {{-- No Data State --}}
            <div class="text-center py-4">
                <x-heroicon-o-cpu-chip class="mx-auto h-8 w-8 text-neutral-400" />
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">No hardware tickets</p>
            </div>
        @endif
    @endif
</div>
