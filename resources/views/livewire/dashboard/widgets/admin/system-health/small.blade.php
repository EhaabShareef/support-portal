<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-cpu-chip class="h-4 w-4 text-emerald-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">System Health</h3>
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
                <p class="text-xs">Health check failed</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading State --}}
        <div class="flex items-center justify-center h-16" aria-busy="true" role="status" aria-label="Loading system health">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-emerald-500"></div>
        </div>
    @else
        {{-- Overall Status Display --}}
        <div class="text-center">
            <div class="flex items-center justify-center mb-3">
                @if($overallStatus === 'healthy')
                    <div class="flex items-center gap-2 text-green-600 dark:text-green-400">
                        <x-heroicon-o-check-circle class="h-8 w-8" />
                        <span class="text-lg font-bold">Healthy</span>
                    </div>
                @elseif($overallStatus === 'warning')
                    <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-exclamation-triangle class="h-8 w-8" />
                        <span class="text-lg font-bold">Warning</span>
                    </div>
                @else
                    <div class="flex items-center gap-2 text-red-600 dark:text-red-400">
                        <x-heroicon-o-x-circle class="h-8 w-8" />
                        <span class="text-lg font-bold">Issues</span>
                    </div>
                @endif
            </div>
            
            {{-- Quick Status Indicators --}}
            <div class="grid grid-cols-2 gap-1 text-xs">
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full {{ $healthData['database_status'] === 'healthy' ? 'bg-green-500' : ($healthData['database_status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <span class="text-neutral-600 dark:text-neutral-400">DB</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full {{ $healthData['cache_status'] === 'healthy' ? 'bg-green-500' : ($healthData['cache_status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <span class="text-neutral-600 dark:text-neutral-400">Cache</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full {{ $healthData['queue_status'] === 'healthy' ? 'bg-green-500' : ($healthData['queue_status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <span class="text-neutral-600 dark:text-neutral-400">Queue</span>
                </div>
                <div class="flex items-center gap-1">
                    <div class="w-2 h-2 rounded-full {{ $healthData['storage_status'] === 'healthy' ? 'bg-green-500' : ($healthData['storage_status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <span class="text-neutral-600 dark:text-neutral-400">Storage</span>
                </div>
            </div>
        </div>
    @endif
</div>