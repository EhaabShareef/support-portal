@props(['showDetails' => false])

@php
    $progressTracker = app(\App\Services\SettingsProgressTracker::class);
    $overallProgress = $progressTracker->getOverallProgress();
    $modules = $progressTracker->getAllModules();
@endphp

<div class="bg-white dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Settings Implementation Progress</h3>
        <div class="flex items-center gap-2">
            <span class="text-sm text-neutral-500 dark:text-neutral-400">
                {{ $overallProgress['completed_modules'] }}/{{ $overallProgress['total_modules'] }} Complete
            </span>
            <span class="text-2xl font-bold text-sky-600 dark:text-sky-400">
                {{ $overallProgress['overall_progress'] }}%
            </span>
        </div>
    </div>

    {{-- Overall Progress Bar --}}
    <div class="mb-6">
        <x-settings.progress-bar 
            :progress="$overallProgress['overall_progress']" 
            :status="$overallProgress['status']"
            :showPercentage="false"
            size="lg"
        />
    </div>

    @if($showDetails)
        {{-- Module Details --}}
        <div class="space-y-4">
            @foreach($modules as $moduleKey => $module)
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <x-dynamic-component :component="$module['icon']" class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $module['name'] }}</span>
                        </div>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ $module['overall_progress'] }}%</span>
                    </div>
                    
                    <x-settings.progress-bar 
                        :progress="$module['overall_progress']" 
                        :status="$module['status']"
                        :showPercentage="false"
                        size="sm"
                    />

                    {{-- Section Details --}}
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach($module['sections'] as $sectionKey => $section)
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-neutral-500 dark:text-neutral-400">{{ $section['name'] }}</span>
                                <div class="flex items-center gap-1">
                                    @if($section['status'] === 'completed')
                                        <x-heroicon-o-check-circle class="h-3 w-3 text-green-500" />
                                    @elseif($section['status'] === 'in_progress')
                                        <x-heroicon-o-arrow-path class="h-3 w-3 text-sky-500" />
                                    @else
                                        <x-heroicon-o-clock class="h-3 w-3 text-neutral-400" />
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
