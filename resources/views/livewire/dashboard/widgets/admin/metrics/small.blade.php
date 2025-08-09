<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <x-heroicon-o-chart-bar class="h-4 w-4 text-blue-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Admin Metrics</h3>
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
        <div class="space-y-2" aria-busy="true" role="status" aria-label="Loading metrics">
            <div class="flex justify-between">
                <div class="animate-pulse h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-16"></div>
                <div class="animate-pulse h-6 bg-neutral-300 dark:bg-neutral-600 rounded w-12"></div>
            </div>
            <div class="flex justify-between">
                <div class="animate-pulse h-3 bg-neutral-200 dark:bg-neutral-700 rounded w-20"></div>
                <div class="animate-pulse h-6 bg-neutral-300 dark:bg-neutral-600 rounded w-8"></div>
            </div>
        </div>
    @else
        {{-- Compact Metrics --}}
        <div class="space-y-2">
            <div class="flex justify-between items-center">
                <span class="text-xs text-neutral-600 dark:text-neutral-400">Total</span>
                <span class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                    {{ number_format($metrics['total_tickets']) }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-xs text-neutral-600 dark:text-neutral-400">Open</span>
                <span class="text-lg font-bold text-red-600 dark:text-red-400">
                    {{ number_format($metrics['open_tickets']) }}
                </span>
            </div>
        </div>
    @endif
</div>