<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-ticket class="h-4 w-4 text-purple-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">My Tickets</h3>
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
        <div class="space-y-3" aria-busy="true" role="status" aria-label="Loading my tickets">
            <div class="animate-pulse">
                <div class="h-8 bg-neutral-200 dark:bg-neutral-700 rounded mb-2"></div>
                <div class="h-4 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
            </div>
        </div>
    @else
        {{-- Main Metric --}}
        <div class="text-center mb-4">
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mb-1">
                {{ number_format($ticketData['open_tickets']) }}
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">Open Tickets</div>
        </div>
        
        {{-- Quick Stats --}}
        <div class="space-y-2 text-xs">
            <div class="flex justify-between items-center">
                <span class="text-neutral-600 dark:text-neutral-400">Total</span>
                <span class="font-medium">{{ number_format($ticketData['total_tickets']) }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-neutral-600 dark:text-neutral-400">Resolved</span>
                <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($ticketData['resolved_tickets']) }}</span>
            </div>
            @if($ticketData['recent_ticket'])
                <div class="pt-2 border-t border-neutral-200/20 dark:border-neutral-600/30">
                    <div class="text-neutral-500 dark:text-neutral-400 mb-1">Latest:</div>
                    <div class="font-medium text-neutral-700 dark:text-neutral-300 truncate">
                        #{{ $ticketData['recent_ticket']->ticket_number }}
                    </div>
                    <div class="text-neutral-500 dark:text-neutral-400">
                        {{ $ticketData['recent_ticket']->created_at->diffForHumans() }}
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>