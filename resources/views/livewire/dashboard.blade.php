<div class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-chart-bar-square class="h-8 w-8 text-sky-500" />
                    Dashboard
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Welcome back, {{ auth()->user()->name }} | {{ ucfirst($userRole) }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="openCustomizeModal" 
                    onclick="console.log('Customize button clicked')"
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

    {{-- Widget-Based Dashboard --}}
    @if($userWidgets->isNotEmpty())
        <div class="dashboard-grid grid gap-6 auto-rows-min grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($userWidgets as $userWidget)
                @php
                    $widget = $userWidget->widget;
                    $size = $userWidget->size;
                    $componentName = $widget->getComponentForSize($size);
                @endphp
                
                <div class="{{ $this->getWidgetClasses($size) }}" wire:key="widget-{{ $widget->id }}">
                    {{-- Render the actual widget component --}}
                    @livewire($componentName, [], key('widget-' . $widget->id . '-' . $size))
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
