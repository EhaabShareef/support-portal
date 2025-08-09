<div class="h-full p-6 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <x-heroicon-o-chart-bar class="h-6 w-6 text-blue-500" />
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">Admin Dashboard Metrics</h3>
        </div>
        <button wire:click="refreshData" 
                class="p-2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
            <x-heroicon-o-arrow-path class="h-5 w-5" />
        </button>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="flex items-center justify-center h-48 text-red-500">
            <div class="text-center">
                <x-heroicon-o-exclamation-triangle class="h-12 w-12 mx-auto mb-4" />
                <p class="text-sm">Failed to load dashboard metrics</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-6" aria-busy="true" role="status" aria-label="Loading metrics">
            <div class="grid grid-cols-3 gap-6">
                @for($i = 0; $i < 6; $i++)
                    <div class="animate-pulse">
                        <div class="h-5 bg-neutral-200 dark:bg-neutral-700 rounded mb-3"></div>
                        <div class="h-10 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        {{-- Comprehensive Metrics Grid --}}
        <div class="grid grid-cols-3 gap-6">
            {{-- Total Tickets --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                    {{ number_format($metrics['total_tickets']) }}
                </div>
                <div class="text-sm text-neutral-500">Total Tickets</div>
            </div>

            {{-- Open Tickets --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">
                    {{ number_format($metrics['open_tickets']) }}
                </div>
                <div class="text-sm text-neutral-500">Open Tickets</div>
            </div>

            {{-- Organizations --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                    {{ number_format($metrics['organizations']) }}
                </div>
                <div class="text-sm text-neutral-500">Organizations</div>
            </div>

            {{-- Active Users --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">
                    {{ number_format($metrics['active_users']) }}
                </div>
                <div class="text-sm text-neutral-500">Active Users</div>
            </div>

            {{-- Departments --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                    {{ number_format($metrics['departments']) }}
                </div>
                <div class="text-sm text-neutral-500">Departments</div>
            </div>

            {{-- Avg Resolution Time --}}
            <div class="text-center">
                <div class="text-3xl font-bold text-amber-600 dark:text-amber-400 mb-2">
                    {{ $metrics['avg_resolution_time'] }}
                </div>
                <div class="text-sm text-neutral-500">Avg Resolution</div>
            </div>
        </div>

        {{-- Additional Statistics --}}
        <div class="mt-6 pt-6 border-t border-white/10">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="bg-green-100 dark:bg-green-900/30 rounded-lg p-3">
                    <div class="text-lg font-bold text-green-700 dark:text-green-300">
                        {{ $metrics['resolved_today'] }}
                    </div>
                    <div class="text-xs text-green-600 dark:text-green-400">Resolved Today</div>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900/30 rounded-lg p-3">
                    <div class="text-lg font-bold text-blue-700 dark:text-blue-300">
                        {{ $metrics['resolved_this_week'] }}
                    </div>
                    <div class="text-xs text-blue-600 dark:text-blue-400">Resolved This Week</div>
                </div>
            </div>
        </div>
    @endif
</div>