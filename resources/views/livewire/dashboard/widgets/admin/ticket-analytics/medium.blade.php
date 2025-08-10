<div class="h-full p-4 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <x-heroicon-o-chart-bar class="h-4 w-4 text-blue-500" />
            <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Ticket Analytics</h3>
        </div>
        <button wire:click="refreshData" 
                class="p-1 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
            <x-heroicon-o-arrow-path class="h-3 w-3" />
        </button>
    </div>

    {{-- Widget Content --}}
    @if($hasError)
        <div class="mt-8 p-6 border border-neutral-300 dark:border-neutral-700 rounded-xl bg-neutral-50 dark:bg-neutral-800 text-center shadow-sm">
            <div class="flex flex-col items-center space-y-3">
                <!-- Icon -->
                <x-heroicon-o-wrench-screwdriver class="w-10 h-10 text-amber-500" />

                <!-- Message -->
                <p class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                    🚧 Widget still in beta brew...
                </p>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Our code gnomes are busy casting functions. 🧙‍♂️
                </p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading State --}}
        <div class="space-y-4" aria-busy="true" role="status" aria-label="Loading ticket analytics">
            <div class="animate-pulse">
                <div class="flex space-x-4 mb-4">
                    <div class="h-16 bg-neutral-200 dark:bg-neutral-700 rounded flex-1"></div>
                    <div class="h-16 bg-neutral-200 dark:bg-neutral-700 rounded flex-1"></div>
                </div>
                <div class="space-y-2">
                    <div class="h-4 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                    <div class="h-4 bg-neutral-300 dark:bg-neutral-600 rounded w-3/4"></div>
                    <div class="h-4 bg-neutral-300 dark:bg-neutral-600 rounded w-1/2"></div>
                </div>
            </div>
        </div>
    @else
        {{-- Main Metrics Row --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="text-center">
                <div class="text-xl font-bold text-blue-600 dark:text-blue-400 mb-1">
                    {{ number_format($analytics['today_tickets']) }}
                </div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Today</div>
                @if($analytics['daily_trend'] != 0)
                    <div class="text-xs {{ $analytics['daily_trend'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $analytics['daily_trend'] > 0 ? '↑' : '↓' }} {{ abs($analytics['daily_trend']) }}%
                    </div>
                @endif
            </div>
            <div class="text-center">
                <div class="text-xl font-bold text-purple-600 dark:text-purple-400 mb-1">
                    {{ number_format($analytics['this_week_tickets']) }}
                </div>
                <div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">This Week</div>
                @if($analytics['weekly_trend'] != 0)
                    <div class="text-xs {{ $analytics['weekly_trend'] > 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $analytics['weekly_trend'] > 0 ? '↑' : '↓' }} {{ abs($analytics['weekly_trend']) }}%
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Status Breakdown --}}
        <div class="mb-4">
            <div class="text-xs font-medium text-neutral-600 dark:text-neutral-400 mb-2">Status Distribution</div>
            <div class="grid grid-cols-2 gap-2 text-xs">
                @foreach($analytics['status_breakdown'] as $status => $count)
                    <div class="flex justify-between items-center">
                        <span class="capitalize text-neutral-600 dark:text-neutral-400">{{ $status }}</span>
                        <span class="font-medium 
                            @if($status === 'open') text-yellow-600 dark:text-yellow-400
                            @elseif($status === 'in_progress') text-blue-600 dark:text-blue-400
                            @elseif($status === 'resolved') text-green-600 dark:text-green-400
                            @elseif($status === 'closed') text-gray-600 dark:text-gray-400
                            @endif">{{ number_format($count) }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Priority & Performance --}}
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <div class="font-medium text-neutral-600 dark:text-neutral-400 mb-2">Priority</div>
                <div class="space-y-1">
                    @foreach($analytics['priority_breakdown'] as $priority => $count)
                        <div class="flex justify-between">
                            <span class="capitalize 
                                @if($priority === 'high') text-red-600 dark:text-red-400
                                @elseif($priority === 'medium') text-yellow-600 dark:text-yellow-400
                                @elseif($priority === 'low') text-green-600 dark:text-green-400
                                @endif">{{ $priority }}</span>
                            <span class="font-medium">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <div class="font-medium text-neutral-600 dark:text-neutral-400 mb-2">Performance</div>
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-400">Total</span>
                        <span class="font-medium">{{ number_format($analytics['total_tickets']) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600 dark:text-neutral-400">Avg Resolution</span>
                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ $analytics['avg_resolution_time'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
