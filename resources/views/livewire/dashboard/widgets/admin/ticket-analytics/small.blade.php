<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-chart-bar class="h-4 w-4 text-blue-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Tickets</h3>
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
        {{-- Loading State --}}
        <div class="space-y-3" aria-busy="true" role="status" aria-label="Loading ticket analytics">
            <div class="animate-pulse">
                <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded mb-2"></div>
                <div class="h-4 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
            </div>
        </div>
    @else
        {{-- Main Metric --}}
        <div class="text-center mb-4">
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                {{ number_format($analytics['today_tickets']) }}
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">Today</div>
        </div>
        
        {{-- Quick Stats --}}
        <div class="space-y-2 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-neutral-600 dark:text-neutral-400">Total</span>
                <span class="font-medium">{{ number_format($analytics['total_tickets']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-neutral-600 dark:text-neutral-400">Open</span>
                <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($analytics['open_tickets']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-neutral-600 dark:text-neutral-400">Trend</span>
                <div class="flex items-center gap-1">
                    @if($analytics['trend'] === 'up')
                        <x-heroicon-o-arrow-trending-up class="h-3 w-3 text-green-500" />
                        <span class="text-green-600 dark:text-green-400">+{{ $analytics['trend_percent'] }}%</span>
                    @elseif($analytics['trend'] === 'down')
                        <x-heroicon-o-arrow-trending-down class="h-3 w-3 text-red-500" />
                        <span class="text-red-600 dark:text-red-400">-{{ $analytics['trend_percent'] }}%</span>
                    @else
                        <x-heroicon-o-minus class="h-3 w-3 text-neutral-500" />
                        <span class="text-neutral-500">Stable</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>