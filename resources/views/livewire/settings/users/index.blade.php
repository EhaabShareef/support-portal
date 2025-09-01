<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-users class="h-8 w-8" />
                    User Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage department groups and departments for user access control</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('settings') }}" 
                   class="inline-flex items-center px-4 py-2 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-lg transition-all duration-200 hover:scale-105">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-2" />
                    Back to Settings
                </a>
            </div>
        </div>
    </div>

    {{-- Mobile Navigation Toggle --}}
    <div class="lg:hidden">
        <button 
            wire:click="$toggle('showMobileNav')" 
            class="w-full flex items-center justify-between p-4 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 shadow-sm hover:shadow-md transition-all duration-200">
            <div class="flex items-center gap-3">
                @switch($section)
                    @case('dep_groups')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-rectangle-group class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Dep Groups</span>
                        @break
                    @case('departments')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-building-office-2 class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Departments</span>
                        @break
                @endswitch
            </div>
            <x-heroicon-o-chevron-down class="h-5 w-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200 {{ $showMobileNav ?? false ? 'rotate-180' : '' }}" />
        </button>
        @if($showMobileNav)
            <div class="mt-2 bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 divide-y divide-neutral-200 dark:divide-neutral-700">
                <button wire:click="setSection('dep_groups')" class="w-full text-left px-4 py-3 text-sm {{ $section==='dep_groups' ? 'bg-neutral-50 dark:bg-neutral-700/50' : '' }}">Dep Groups</button>
                <button wire:click="setSection('departments')" class="w-full text-left px-4 py-3 text-sm {{ $section==='departments' ? 'bg-neutral-50 dark:bg-neutral-700/50' : '' }}">Departments</button>
            </div>
        @endif
    </div>

    <div class="lg:flex lg:gap-8">
        {{-- Desktop Navigation --}}
        <nav class="hidden lg:block lg:w-1/4">
            <div class="sticky top-6">
                <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 shadow-sm p-4">
                    <h3 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wide mb-4">Settings Sections</h3>
                    <ul class="space-y-2">
                        <li>
                            <button 
                                wire:click="setSection('dep_groups')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200 group',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'dep_groups',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'dep_groups'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <x-heroicon-o-rectangle-group class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Dep Groups</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Manage department groups</div>
                                </div>
                                @if($section === 'dep_groups')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                        <li>
                            <button 
                                wire:click="setSection('departments')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'departments',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'departments'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                                    <x-heroicon-o-building-office-2 class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Departments</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Manage departments</div>
                                </div>
                                @if($section === 'departments')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        {{-- Content Area --}}
        <div class="lg:flex-1">
            @if($section === 'dep_groups')
                @livewire('settings.users.department-groups')
            @elseif($section === 'departments')
                @livewire('settings.users.departments')
            @endif
        </div>
    </div>
</div>
