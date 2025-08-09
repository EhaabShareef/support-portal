<div class="h-full p-6 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <x-heroicon-o-cpu-chip class="h-6 w-6 text-emerald-500" />
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">System Health</h3>
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
                <p class="text-sm">Failed to load system health</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-4" aria-busy="true" role="status" aria-label="Loading system health">
            @for($i = 0; $i < 4; $i++)
                <div class="animate-pulse">
                    <div class="flex items-center justify-between p-3 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="h-6 w-6 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                            <div class="h-4 w-24 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                        </div>
                        <div class="h-4 w-16 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                    </div>
                </div>
            @endfor
        </div>
    @else
        {{-- Overall Status Header --}}
        <div class="mb-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
                @if($overallStatus === 'healthy')
                    <x-heroicon-o-check-circle class="h-8 w-8 text-green-500" />
                    <span class="text-xl font-bold text-green-600 dark:text-green-400">System Healthy</span>
                @elseif($overallStatus === 'warning')
                    <x-heroicon-o-exclamation-triangle class="h-8 w-8 text-amber-500" />
                    <span class="text-xl font-bold text-amber-600 dark:text-amber-400">System Warning</span>
                @else
                    <x-heroicon-o-x-circle class="h-8 w-8 text-red-500" />
                    <span class="text-xl font-bold text-red-600 dark:text-red-400">System Issues</span>
                @endif
            </div>
            @if(isset($healthData['uptime']))
                <p class="text-sm text-neutral-500 dark:text-neutral-400">Uptime: {{ $healthData['uptime'] }}</p>
            @endif
        </div>

        {{-- Health Components --}}
        <div class="space-y-3">
            {{-- Database Health --}}
            <div class="flex items-center justify-between p-3 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['database']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['database']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <div>
                        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Database</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $healthData['database']['message'] }}</p>
                    </div>
                </div>
                <span class="text-xs text-neutral-500">{{ $healthData['database']['response_time'] ?? 'N/A' }}</span>
            </div>

            {{-- Cache Health --}}
            <div class="flex items-center justify-between p-3 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['cache']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['cache']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <div>
                        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Cache</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $healthData['cache']['message'] }}</p>
                    </div>
                </div>
                <span class="text-xs text-neutral-500">{{ $healthData['cache']['driver'] ?? 'N/A' }}</span>
            </div>

            {{-- Storage Health --}}
            <div class="flex items-center justify-between p-3 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['storage']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['storage']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <div>
                        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Storage</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $healthData['storage']['message'] }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $healthData['storage']['usage_percent'] }}% used</p>
                    <p class="text-xs text-neutral-500">{{ $healthData['storage']['free_space'] }} free</p>
                </div>
            </div>

            {{-- Memory Health --}}
            <div class="flex items-center justify-between p-3 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['memory']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['memory']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <div>
                        <p class="text-sm font-medium text-neutral-800 dark:text-neutral-200">Memory</p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $healthData['memory']['current'] }} / {{ $healthData['memory']['limit'] }}</p>
                    </div>
                </div>
                <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $healthData['memory']['usage_percent'] }}%</span>
            </div>
        </div>
    @endif
</div>