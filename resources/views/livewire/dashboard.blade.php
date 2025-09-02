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
                            <strong>🔧 Hardware Ticket Management:</strong> Complete hardware linking system with progress tracking • <strong>Smart Progress Tracking:</strong> Visual progress bars and real-time hardware fix status • <strong>Intuitive Hardware Modal:</strong> Easy hardware selection with quantity and maintenance note management • <strong>Department Protection:</strong> Prevents department changes when hardware is linked • <strong>Progress Visualization:</strong> Beautiful progress graphs in conversation threads • <strong>Flexible Fixing:</strong> Mark partial quantities as fixed with validation
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

    {{-- Primary User Warning Banner --}}
    @if(auth()->user()->hasRole('admin') || auth()->user()->can('organizations.read'))
        @php
            $organizationsWithoutPrimary = \App\Models\Organization::where('is_active', true)
                ->whereNull('primary_user_id')
                ->get();
        @endphp
        
        @if($organizationsWithoutPrimary->count() > 0)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-1">
                            Primary User Required
                        </h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mb-2">
                            {{ $organizationsWithoutPrimary->count() }} organization(s) do not have a primary user set. 
                            Primary users provide contact information for their organizations.
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach($organizationsWithoutPrimary->take(3) as $org)
                                <a href="{{ route('organizations.show', $org) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 hover:bg-amber-200 dark:hover:bg-amber-900/60 transition-colors duration-200">
                                    {{ $org->name }}
                                </a>
                            @endforeach
                            @if($organizationsWithoutPrimary->count() > 3)
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                    +{{ $organizationsWithoutPrimary->count() - 3 }} more
                                </span>
                            @endif
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('organizations.index') }}" 
                               class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                View All Organizations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
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
