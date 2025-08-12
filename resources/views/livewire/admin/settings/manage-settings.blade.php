<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-cog-6-tooth class="h-8 w-8" />
                    Application Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage departments, groups, and system settings</p>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Tab Navigation --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
        <div class="border-b border-neutral-200 dark:border-neutral-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button wire:click="setActiveTab('application')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'application' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5 mr-2 inline" />
                    Application Settings
                </button>
                <button wire:click="setActiveTab('department-groups')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'department-groups' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-rectangle-group class="h-5 w-5 mr-2 inline" />
                    Department Groups
                </button>
                <button wire:click="setActiveTab('departments')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'departments' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-building-office class="h-5 w-5 mr-2 inline" />
                    Departments
                </button>
                <button wire:click="setActiveTab('schedule-events')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'schedule-events' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-calendar-days class="h-5 w-5 mr-2 inline" />
                    Schedule Events
                </button>
                <button wire:click="setActiveTab('ticket-colors')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'ticket-colors' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-swatch class="h-5 w-5 mr-2 inline" />
                    Ticket Colors
                </button>
                <button wire:click="setActiveTab('hotlines')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'hotlines' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-phone class="h-5 w-5 mr-2 inline" />
                    Support Hotlines
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- Application Settings Tab --}}
            @if($activeTab === 'application')
                @livewire('admin.settings.tabs.application-settings')
            @endif

            {{-- Department Groups Tab --}}
            @if($activeTab === 'department-groups')
                @livewire('admin.settings.tabs.department-groups')
            @endif

            {{-- Departments Tab --}}
            @if($activeTab === 'departments')
                @livewire('admin.settings.tabs.departments')
            @endif

            {{-- Schedule Events Tab --}}
            @if($activeTab === 'schedule-events')
                @livewire('admin.settings.tabs.schedule-event-types')
            @endif

            {{-- Ticket Colors Tab --}}
            @if($activeTab === 'ticket-colors')
                @livewire('admin.settings.tabs.ticket-colors')
            @endif

            {{-- Hotlines Tab --}}
            @if($activeTab === 'hotlines')
                @livewire('admin.settings.tabs.hotlines')
            @endif
        </div>
    </div>
</div>