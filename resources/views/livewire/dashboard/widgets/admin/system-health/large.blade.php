<div class="h-full p-6 bg-white/5 dark:bg-neutral-800 backdrop-blur-md border border-white/20 rounded-lg shadow-md">
    {{-- Widget Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <x-heroicon-o-cpu-chip class="h-6 w-6 text-emerald-500" />
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-200">System Health Monitor</h3>
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
                <p class="text-sm">Failed to load system health monitoring</p>
            </div>
        </div>
    @elseif(!$dataLoaded)
        {{-- Loading Skeleton --}}
        <div class="space-y-6" aria-busy="true" role="status" aria-label="Loading system health">
            <div class="grid grid-cols-2 gap-6">
                @for($i = 0; $i < 6; $i++)
                    <div class="animate-pulse p-4 bg-neutral-100 dark:bg-neutral-700 rounded-lg">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="h-4 w-4 bg-neutral-300 dark:bg-neutral-600 rounded-full"></div>
                            <div class="h-4 w-24 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                        </div>
                        <div class="space-y-2">
                            <div class="h-3 w-full bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                            <div class="h-3 w-3/4 bg-neutral-300 dark:bg-neutral-600 rounded"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        {{-- Overall Status Header --}}
        <div class="mb-6 text-center border-b border-neutral-200/20 dark:border-neutral-600/30 pb-4">
            <div class="flex items-center justify-center gap-3 mb-3">
                @if($overallStatus === 'healthy')
                    <x-heroicon-o-check-circle class="h-10 w-10 text-green-500" />
                    <div class="text-left">
                        <h4 class="text-2xl font-bold text-green-600 dark:text-green-400">System Healthy</h4>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">All systems operational</p>
                    </div>
                @elseif($overallStatus === 'warning')
                    <x-heroicon-o-exclamation-triangle class="h-10 w-10 text-amber-500" />
                    <div class="text-left">
                        <h4 class="text-2xl font-bold text-amber-600 dark:text-amber-400">System Warning</h4>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Some components need attention</p>
                    </div>
                @else
                    <x-heroicon-o-x-circle class="h-10 w-10 text-red-500" />
                    <div class="text-left">
                        <h4 class="text-2xl font-bold text-red-600 dark:text-red-400">System Issues</h4>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">Critical components failing</p>
                    </div>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Uptime:</span>
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $performanceMetrics['uptime'] ?? 'Unknown' }}</span>
                </div>
                <div>
                    <span class="text-neutral-500 dark:text-neutral-400">Active Sessions:</span>
                    <span class="font-medium text-neutral-700 dark:text-neutral-300">{{ $performanceMetrics['active_sessions'] ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- System Health Components Grid --}}
        <div class="grid grid-cols-2 gap-4 mb-6">
            {{-- Database Health --}}
            <div class="p-4 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['database']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['database']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <h5 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Database</h5>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Response:</span>
                        <span class="font-medium">{{ $healthData['database']['response_time'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Query:</span>
                        <span class="font-medium">{{ $healthData['database']['query_time'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Connections:</span>
                        <span class="font-medium">{{ $healthData['database']['connections'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Cache Health --}}
            <div class="p-4 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['cache']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['cache']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <h5 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Cache</h5>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Write:</span>
                        <span class="font-medium">{{ $healthData['cache']['write_time'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Read:</span>
                        <span class="font-medium">{{ $healthData['cache']['read_time'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Driver:</span>
                        <span class="font-medium">{{ $healthData['cache']['driver'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Memory Health --}}
            <div class="p-4 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['memory']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['memory']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <h5 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Memory</h5>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Usage:</span>
                        <span class="font-medium">{{ $healthData['memory']['usage_percent'] }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Current:</span>
                        <span class="font-medium">{{ $healthData['memory']['current'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Peak:</span>
                        <span class="font-medium">{{ $healthData['memory']['peak'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Storage Health --}}
            <div class="p-4 bg-white/5 dark:bg-neutral-700/50 rounded-lg border border-neutral-200/20 dark:border-neutral-600/30">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 rounded-full {{ $healthData['storage']['status'] === 'healthy' ? 'bg-green-500' : ($healthData['storage']['status'] === 'warning' ? 'bg-amber-500' : 'bg-red-500') }}"></div>
                    <h5 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Storage</h5>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Usage:</span>
                        <span class="font-medium">{{ $healthData['storage']['usage_percent'] }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Free:</span>
                        <span class="font-medium">{{ $healthData['storage']['free_space'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Used:</span>
                        <span class="font-medium">{{ $healthData['storage']['used_space'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Additional System Info --}}
        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-200/20 dark:border-neutral-600/30">
            {{-- PHP Info --}}
            <div>
                <h6 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">PHP Environment</h6>
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Version:</span>
                        <span class="font-medium">{{ $healthData['php']['version'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Extensions:</span>
                        <span class="font-medium">{{ $healthData['php']['extensions_loaded'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Max Execution:</span>
                        <span class="font-medium">{{ $healthData['php']['max_execution_time'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Performance Metrics --}}
            <div>
                <h6 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200 mb-2">Performance</h6>
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Load (1m):</span>
                        <span class="font-medium">{{ $performanceMetrics['load_average']['1min'] ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">DB Size:</span>
                        <span class="font-medium">{{ $performanceMetrics['database_size'] ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Recent Errors:</span>
                        <span class="font-medium">{{ $performanceMetrics['recent_errors'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>