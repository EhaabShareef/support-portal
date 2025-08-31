<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-ticket class="h-8 w-8" />
                    Ticket Settings
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure ticket workflow, priorities, statuses, and attachments</p>
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
                    @case('workflow')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Workflow</span>
                        @break
                    @case('attachments')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-paper-clip class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Attachments</span>
                        @break
                    @case('priority')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Priority</span>
                        @break
                    @case('status')
                        <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                        </div>
                        <span class="font-medium text-neutral-800 dark:text-neutral-100">Status</span>
                        @break
                @endswitch
            </div>
            <x-heroicon-o-chevron-down class="h-5 w-5 text-neutral-500 dark:text-neutral-400 transition-transform duration-200 {{ $showMobileNav ?? false ? 'rotate-180' : '' }}" />
        </button>
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
                                wire:click="setSection('workflow')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200 group',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'workflow',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'workflow'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Workflow</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Ticket workflow settings</div>
                                </div>
                                @if($section === 'workflow')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                        
                        <li>
                            <button 
                                wire:click="setSection('attachments')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200 group',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'attachments',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'attachments'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <x-heroicon-o-paper-clip class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Attachments</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">File upload settings</div>
                                </div>
                                @if($section === 'attachments')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                        
                        <li>
                            <button 
                                wire:click="setSection('priority')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200 group',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'priority',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'priority'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Priority</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Priority color management</div>
                                </div>
                                @if($section === 'priority')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                        
                        <li>
                            <button 
                                wire:click="setSection('status')" 
                                @class([
                                    'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200 group',
                                    'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'status',
                                    'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'status'
                                ])>
                                <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                    <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium">Status</div>
                                    <div class="text-xs text-neutral-500 dark:text-neutral-400">Status management</div>
                                </div>
                                @if($section === 'status')
                                    <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                @endif
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        {{-- Mobile Navigation Dropdown --}}
        <nav class="lg:hidden" x-show="$wire.showMobileNav" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform -translate-y-2">
            <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 shadow-sm p-4 mt-2">
                <ul class="space-y-2">
                    <li>
                        <button 
                            wire:click="setSection('workflow')" 
                            @class([
                                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200',
                                'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'workflow',
                                'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'workflow'
                            ])>
                            <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-cog-6-tooth class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Workflow</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Ticket workflow settings</div>
                            </div>
                            @if($section === 'workflow')
                                <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            @endif
                        </button>
                    </li>
                    
                    <li>
                        <button 
                            wire:click="setSection('attachments')" 
                            @class([
                                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200',
                                'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'attachments',
                                'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'attachments'
                            ])>
                            <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-paper-clip class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Attachments</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">File upload settings</div>
                            </div>
                            @if($section === 'attachments')
                                <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            @endif
                        </button>
                    </li>
                    
                    <li>
                        <button 
                            wire:click="setSection('priority')" 
                            @class([
                                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200',
                                'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'priority',
                                'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'priority'
                            ])>
                            <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Priority</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Priority color management</div>
                            </div>
                            @if($section === 'priority')
                                <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            @endif
                        </button>
                    </li>
                    
                    <li>
                        <button 
                            wire:click="setSection('status')" 
                            @class([
                                'w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all duration-200',
                                'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' => $section === 'status',
                                'hover:bg-neutral-50 dark:hover:bg-neutral-700/50 text-neutral-700 dark:text-neutral-300' => $section !== 'status'
                            ])>
                            <div class="h-8 w-8 bg-neutral-100 dark:bg-neutral-700 rounded-lg flex items-center justify-center">
                                <x-heroicon-o-flag class="h-4 w-4 text-neutral-600 dark:text-neutral-400" />
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Status</div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">Status management</div>
                            </div>
                            @if($section === 'status')
                                <x-heroicon-o-check class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
        </nav>

        {{-- Content Area --}}
        <div class="lg:flex-1">
            @switch($section)
                @case('attachments')
                    @livewire('settings.tickets.attachments')
                    @break
                @case('priority')
                    @livewire('settings.tickets.priority')
                    @break
                @case('status')
                    @livewire('settings.tickets.status')
                    @break
                @default
                    @livewire('settings.tickets.workflow')
            @endswitch
        </div>
    </div>
</div>
