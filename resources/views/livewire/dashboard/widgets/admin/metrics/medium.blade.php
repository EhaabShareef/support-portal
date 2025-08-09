<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-500" />
            <h3 class="font-medium text-neutral-800 dark:text-neutral-200">Admin Metrics</h3>
        </div>
        <button wire:click="refreshData" 
                class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
            <x-heroicon-o-arrow-path class="h-4 w-4" />
        </button>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-32 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-8 w-8 mx-auto mb-2" />
                <p class="text-xs">Failed to load data</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-4" aria-busy="true" role="status" aria-label="Loading metrics">
            <div class="grid grid-cols-2 gap-4">
                @for($i = 0; $i < 4; $i++)
                    <div class="animate-pulse">
                        <div class="h-4 bg-neutral-200 dark:bg-neutral-700 rounded mb-2"></div>
                        <div class="h-8 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        {{-- Metrics Grid --}}
        <div class="grid grid-cols-2 gap-4">
            {{-- Total Tickets --}}
            <div class="text-center">
                <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">
                    {{ number_format($metrics['total_tickets']) }}
                </div>
                <div class="text-xs text-neutral-500">Total Tickets</div>
            </div>

            {{-- Open Tickets --}}
            <div class="text-center">
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ number_format($metrics['open_tickets']) }}
                </div>
                <div class="text-xs text-neutral-500">Open Tickets</div>
            </div>

            {{-- Organizations --}}
            <div class="text-center">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ number_format($metrics['organizations']) }}
                </div>
                <div class="text-xs text-neutral-500">Organizations</div>
            </div>

            {{-- Active Users --}}
            <div class="text-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ number_format($metrics['active_users']) }}
                </div>
                <div class="text-xs text-neutral-500">Active Users</div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="mt-4 pt-4 border-t border-white/10">
            <div class="text-center text-sm text-green-600 dark:text-green-400">
                {{ $metrics['resolved_today'] }} tickets resolved today
            </div>
        </div>
    @endif
</div>