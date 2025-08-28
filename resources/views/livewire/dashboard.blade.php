<div class="space-y-6">
    {{-- Page Header --}}
    <div
        class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1
                    class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-chart-bar-square class="h-8 w-8 text-sky-500" />
                    Dashboard
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Welcome back, {{ auth()->user()->name }} | {{ ucfirst($userRole) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="openCustomizeModal"
                    class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-2" />
                    Customize
                </button>
                <button wire:click="refreshData"
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- New Features Banner --}}
    @if($showNewFeaturesBanner)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-sparkles class="h-6 w-6 text-blue-500" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900 dark:text-blue-100">
                            🎉 New Features Available!
                        </h3>
                        <p class="text-sm text-blue-800 dark:text-blue-200 mt-1">
                            <strong>🎯 Ticket Splitting:</strong> Split tickets from any conversation message with beautiful modal interface • <strong>Enhanced Action Buttons:</strong> Smart hover expansion effects and context-aware actions • <strong>Table Management:</strong> Compact icon-only actions for data tables with tooltips • <strong>Smart Visibility:</strong> Actions automatically hide when not applicable • <strong>Improved UX:</strong> Professional animations and responsive design across all devices
                        </p>
                    </div>
                </div>
                <button wire:click="dismissNewFeaturesBanner" 
                        class="flex-shrink-0 ml-4 p-1 rounded-md text-blue-400 hover:text-blue-600 dark:text-blue-300 dark:hover:text-blue-100 transition-colors duration-200"
                        title="Dismiss">
                    <x-heroicon-s-x-mark class="h-5 w-5" />
                </button>
            </div>
        </div>
    @endif

    {{-- Widget-Based Dashboard --}}
    @if ($userWidgets->isNotEmpty())
        <div class="dashboard-grid grid gap-6 auto-rows-min grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($userWidgets as $userWidget)
                @php
                    $widget = $userWidget->widget;
                    $size = $userWidget->size;
                    $componentInfo = $widget->getComponentForSizeWithFallback($size);
                @endphp

                <div class="{{ $this->getWidgetClasses($size) }}" wire:key="widget-{{ $widget->id }}">
                    {{-- Render the widget component with fallback support --}}
                    @livewire($componentInfo['component'], $componentInfo['params'], key('widget-' . $widget->id . '-' . $size))
                </div>
            @endforeach
        </div>
    @else
        {{-- No widgets available --}}
        <div class="text-center py-12">
            <x-heroicon-o-squares-plus class="mx-auto h-12 w-12 text-neutral-400" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No widgets configured</h3>
            <p class="mt-1 text-sm text-neutral-500">
                Click "Customize" to configure your dashboard widgets.
            </p>
        </div>
    @endif

    {{-- Customize Dashboard Modal --}}
    <livewire:customize-dashboard />

    {{-- Loading States --}}
    <div wire:loading wire:target="refreshData" class="fixed inset-0 bg-black/20 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 shadow-xl">
            <div class="flex items-center space-x-3">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-sky-600"></div>
                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Updating dashboard...</span>
            </div>
        </div>
    </div>

    {{-- Inline Scripts --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('dataRefreshed', () => {
                console.log('Dashboard data refreshed');
            });
        });
    </script>
</div>
