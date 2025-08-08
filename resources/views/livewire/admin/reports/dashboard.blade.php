<div class="container mx-auto px-4 py-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-neutral-900 dark:text-neutral-100">Reports Dashboard</h1>
        <p class="mt-2 text-neutral-600 dark:text-neutral-400">
            Access comprehensive reports and analytics for your support portal operations.
        </p>
    </div>

    @foreach($reportCategories as $categoryName => $reports)
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-200 mb-4">
                {{ $categoryName }}
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($reports as $report)
                    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-neutral-200 dark:border-neutral-700">
                        <div class="p-6">
                            <div class="flex items-start space-x-3">
                                @php
                                    $iconClasses = 'h-8 w-8 text-blue-600 dark:text-blue-400 flex-shrink-0';
                                @endphp
                                
                                @switch($report['icon'])
                                    @case('chart-bar')
                                        <x-heroicon-o-chart-bar class="{{ $iconClasses }}" />
                                        @break
                                    @case('clock')
                                        <x-heroicon-o-clock class="{{ $iconClasses }}" />
                                        @break
                                    @case('user-group')
                                        <x-heroicon-o-user-group class="{{ $iconClasses }}" />
                                        @break
                                    @case('chart-pie')
                                        <x-heroicon-o-chart-pie class="{{ $iconClasses }}" />
                                        @break
                                    @case('exclamation-triangle')
                                        <x-heroicon-o-exclamation-triangle class="{{ $iconClasses }}" />
                                        @break
                                    @case('building-office-2')
                                        <x-heroicon-o-building-office-2 class="{{ $iconClasses }}" />
                                        @break
                                    @case('document-text')
                                        <x-heroicon-o-document-text class="{{ $iconClasses }}" />
                                        @break
                                    @case('currency-dollar')
                                        <x-heroicon-o-currency-dollar class="{{ $iconClasses }}" />
                                        @break
                                    @case('computer-desktop')
                                        <x-heroicon-o-computer-desktop class="{{ $iconClasses }}" />
                                        @break
                                    @case('wrench-screwdriver')
                                        <x-heroicon-o-wrench-screwdriver class="{{ $iconClasses }}" />
                                        @break
                                    @case('squares-2x2')
                                        <x-heroicon-o-squares-2x2 class="{{ $iconClasses }}" />
                                        @break
                                    @case('users')
                                        <x-heroicon-o-users class="{{ $iconClasses }}" />
                                        @break
                                    @case('building-office')
                                        <x-heroicon-o-building-office class="{{ $iconClasses }}" />
                                        @break
                                    @case('chart-line')
                                        <div class="h-8 w-8 text-blue-600 dark:text-blue-400 flex-shrink-0">📈</div>
                                        @break
                                    @case('calendar-days')
                                        <x-heroicon-o-calendar-days class="{{ $iconClasses }}" />
                                        @break
                                    @default
                                        <x-heroicon-o-chart-bar class="{{ $iconClasses }}" />
                                @endswitch
                                
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">
                                        {{ $report['name'] }}
                                    </h3>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">
                                        {{ $report['description'] }}
                                    </p>
                                    
                                    @if(isset($report['available']) && $report['available'])
                                        <a href="{{ route($report['route']) }}" 
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                                            View Report
                                            <x-heroicon-o-arrow-right class="ml-2 h-4 w-4" />
                                        </a>
                                    @else
                                        <button 
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-neutral-400 dark:text-neutral-600 cursor-not-allowed"
                                            disabled
                                        >
                                            Coming Soon
                                            <x-heroicon-o-clock class="ml-2 h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="mt-12 p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
        <div class="flex items-start space-x-3">
            <x-heroicon-o-information-circle class="h-6 w-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
            <div>
                <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-2">
                    About Reports
                </h3>
                <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1">
                    <li>• All reports are read-only and pull data directly from the database</li>
                    <li>• Reports support filtering by date range, organization, department, and other criteria</li>
                    <li>• Export functionality available for CSV and Excel formats</li>
                    <li>• All reports respect role-based access control for data security</li>
                </ul>
            </div>
        </div>
    </div>
</div>