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
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- Application Settings Tab --}}
            @if($activeTab === 'application')
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Application Settings</h3>
                    <button wire:click="createSetting" 
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        New Setting
                    </button>
                </div>

                @if($this->applicationSettings->count() > 0)
                    @foreach($this->applicationSettings as $group => $settings)
                        <div class="mb-8">
                            <h4 class="text-md font-medium text-neutral-700 dark:text-neutral-300 mb-4 capitalize border-b border-neutral-200 dark:border-neutral-700 pb-2">
                                {{ str_replace('_', ' ', $group) }} Settings
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($settings as $setting)
                                    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <h5 class="font-medium text-neutral-800 dark:text-neutral-100">{{ $setting->label }}</h5>
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">{{ $setting->key }}</p>
                                                @if($setting->description)
                                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $setting->description }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1 ml-2">
                                                @if($setting->is_public)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                                        Public
                                                    </span>
                                                @endif
                                                @if($setting->is_encrypted)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300">
                                                        Encrypted
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                {{ $setting->type }}
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <p class="text-sm text-neutral-700 dark:text-neutral-300 break-all">
                                                @if($setting->is_encrypted)
                                                    <span class="text-neutral-500 italic">*** encrypted ***</span>
                                                @elseif($setting->type === 'boolean')
                                                    {{ $setting->value ? 'True' : 'False' }}
                                                @elseif(in_array($setting->type, ['json', 'array']))
                                                    <span class="font-mono text-xs">{{ json_encode($setting->value) }}</span>
                                                @else
                                                    {{ $setting->value ?: 'Not set' }}
                                                @endif
                                            </p>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <button wire:click="editSetting({{ $setting->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded transition-all duration-200">
                                                <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                                                Edit
                                            </button>
                                            <button wire:click="confirmDeleteSetting({{ $setting->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded transition-all duration-200">
                                                <x-heroicon-o-trash class="h-3 w-3 mr-1" />
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-adjustments-horizontal class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No settings found</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first application setting.</p>
                    </div>
                @endif
            @endif

            {{-- Department Groups Tab --}}
            @if($activeTab === 'department-groups')
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Department Groups</h3>
                    <button wire:click="createDeptGroup" 
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        New Group
                    </button>
                </div>

                @if($this->departmentGroups->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->departmentGroups as $group)
                            <div class="glass-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-3">
                                                @if($group->color)
                                                    <div class="w-4 h-4 rounded-full border border-neutral-300 dark:border-neutral-600" style="background-color: {{ $group->color }}"></div>
                                                @endif
                                                <div>
                                                    <h4 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">{{ $group->name }}</h4>
                                                    @if($group->description)
                                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $group->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $group->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                                    {{ $group->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-4 text-sm">
                                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                                <x-heroicon-o-building-office class="h-4 w-4" />
                                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $group->departments_count }}</span> departments
                                            </div>
                                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                                <x-heroicon-o-list-bullet class="h-4 w-4" />
                                                Sort Order: <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $group->sort_order }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 lg:ml-4">
                                        <button wire:click="editDeptGroup({{ $group->id }})"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                            <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </button>
                                        <button wire:click="confirmDeleteDeptGroup({{ $group->id }})"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200">
                                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-rectangle-group class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No department groups found</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first department group.</p>
                    </div>
                @endif
            @endif

            {{-- Departments Tab --}}
            @if($activeTab === 'departments')
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Departments</h3>
                    <button wire:click="createDept" 
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        New Department
                    </button>
                </div>

                @if($this->departments->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->departments as $dept)
                            <div class="glass-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <h4 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">{{ $dept->name }}</h4>
                                                @if($dept->description)
                                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $dept->description }}</p>
                                                @endif
                                                @if($dept->departmentGroup)
                                                    <p class="text-xs text-neutral-500 dark:text-neutral-500 mt-1">
                                                        Group: {{ $dept->departmentGroup->name }}
                                                    </p>
                                                @endif
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $dept->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                                    {{ $dept->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                            @if($dept->email)
                                                <div class="flex items-center gap-1">
                                                    <x-heroicon-o-envelope class="h-4 w-4" />
                                                    {{ $dept->email }}
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-users class="h-4 w-4" />
                                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $dept->users_count }}</span> users
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-ticket class="h-4 w-4" />
                                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $dept->tickets_count }}</span> tickets
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-list-bullet class="h-4 w-4" />
                                                Sort: <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $dept->sort_order }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 lg:ml-4">
                                        <button wire:click="editDept({{ $dept->id }})"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                            <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </button>
                                        <button wire:click="confirmDeleteDept({{ $dept->id }})"
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200">
                                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-building-office class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No departments found</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first department.</p>
                    </div>
                @endif
            @endif

            {{-- Schedule Event Types Tab --}}
            @if($activeTab === 'schedule-events')
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Schedule Event Types</h3>
                    <button wire:click="createEventType" 
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        New Event Type
                    </button>
                </div>

                @if($this->scheduleEventTypes->count() > 0)
                    <div class="space-y-4">
                        @foreach($this->scheduleEventTypes as $eventType)
                            <div class="glass-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <div class="flex-1 space-y-3">
                                        <div class="flex items-start justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-block px-3 py-1 rounded text-sm {{ $eventType->tailwind_classes }} font-medium">
                                                    {{ strtoupper(substr($eventType->label, 0, 3)) }}
                                                </span>
                                                <div>
                                                    <h4 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">{{ $eventType->label }}</h4>
                                                    <p class="text-xs text-neutral-500 dark:text-neutral-500">
                                                        Color: {{ $eventType->color }} | Sort: {{ $eventType->sort_order }}
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $eventType->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                                    {{ $eventType->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                            <div class="flex items-center gap-1">
                                                <x-heroicon-o-calendar-days class="h-4 w-4" />
                                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $eventType->schedules_count ?? 0 }}</span> schedules
                                            </div>
                                            <div class="text-xs">
                                                Created: {{ $eventType->created_at?->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button wire:click="editEventType({{ $eventType->id }})"
                                            class="inline-flex items-center px-3 py-2 bg-neutral-200 hover:bg-neutral-300 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-neutral-800 dark:text-neutral-100 text-sm font-medium rounded-md transition-all duration-200">
                                            <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                                            Edit
                                        </button>
                                        <button wire:click="confirmDeleteEventType({{ $eventType->id }})"
                                            class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 dark:bg-red-900/40 dark:hover:bg-red-900/60 text-red-800 dark:text-red-300 text-sm font-medium rounded-md transition-all duration-200">
                                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No event types found</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first schedule event type.</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modals --}}
    @if($confirmingDeptGroupDelete || $confirmingDeptDelete || $confirmingSettingDelete || $confirmingEventTypeDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                    Confirm Delete
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Are you sure you want to delete this item? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="{{ $confirmingDeptGroupDelete ? 'deleteDeptGroup' : ($confirmingDeptDelete ? 'deleteDept' : ($confirmingSettingDelete ? 'deleteSetting' : 'deleteEventType')) }}"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="cancelDelete"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Department Group Modal --}}
    @if($showDeptGroupModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeptGroupModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="saveDeptGroup">
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $deptGroupEditMode ? 'Edit Department Group' : 'Create Department Group' }}
                                </h3>
                                <button type="button" wire:click="closeDeptGroupModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                    <input type="text" wire:model="deptGroupForm.name" 
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    @error('deptGroupForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                    <textarea wire:model="deptGroupForm.description" rows="3"
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                    @error('deptGroupForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                                        <input type="color" wire:model="deptGroupForm.color" 
                                               class="w-full h-10 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900">
                                        @error('deptGroupForm.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                                        <input type="number" wire:model="deptGroupForm.sort_order" min="0"
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @error('deptGroupForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="deptGroupForm.is_active"
                                               class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                        <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $deptGroupEditMode ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeDeptGroupModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Department Modal --}}
    @if($showDeptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeptModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="saveDept">
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $deptEditMode ? 'Edit Department' : 'Create Department' }}
                                </h3>
                                <button type="button" wire:click="closeDeptModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                    <input type="text" wire:model="deptForm.name" 
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    @error('deptForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                    <textarea wire:model="deptForm.description" rows="3"
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                    @error('deptForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group</label>
                                        <select wire:model="deptForm.department_group_id" 
                                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                            <option value="">Select a group</option>
                                            @foreach($this->availableDeptGroups as $group)
                                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('deptForm.department_group_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                                        <input type="email" wire:model="deptForm.email" 
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @error('deptForm.email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                                        <input type="number" wire:model="deptForm.sort_order" min="0"
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @error('deptForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center">
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="deptForm.is_active"
                                                   class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                            <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $deptEditMode ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeDeptModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Setting Modal --}}
    @if($showSettingModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSettingModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <form wire:submit="saveSetting">
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $settingEditMode ? 'Edit Setting' : 'Create Setting' }}
                                </h3>
                                <button type="button" wire:click="closeSettingModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Key *</label>
                                        <input type="text" wire:model="settingForm.key" 
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               placeholder="e.g. app_name, max_upload_size">
                                        @error('settingForm.key') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Label *</label>
                                        <input type="text" wire:model="settingForm.label" 
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               placeholder="Human readable label">
                                        @error('settingForm.label') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                    <textarea wire:model="settingForm.description" rows="2"
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                              placeholder="Description of what this setting controls"></textarea>
                                    @error('settingForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Type *</label>
                                        <select wire:model="settingForm.type" 
                                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                            <option value="string">String</option>
                                            <option value="integer">Integer</option>
                                            <option value="float">Float</option>
                                            <option value="boolean">Boolean</option>
                                            <option value="json">JSON</option>
                                            <option value="array">Array</option>
                                        </select>
                                        @error('settingForm.type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Group *</label>
                                        <input type="text" wire:model="settingForm.group" 
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               placeholder="e.g. general, mail, uploads">
                                        @error('settingForm.group') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Validation Rules</label>
                                        <input type="text" wire:model="validationRulesText" 
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               placeholder="required|min:1|max:100">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Value</label>
                                    @if($settingForm['type'] === 'boolean')
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="settingForm.value"
                                                   class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                            <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">True/False</span>
                                        </label>
                                    @elseif(in_array($settingForm['type'], ['json', 'array']))
                                        <textarea wire:model="settingForm.value" rows="4"
                                                  class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono text-sm"
                                                  placeholder='{"key": "value"} or ["item1", "item2"]'></textarea>
                                    @else
                                        <input type="{{ $settingForm['type'] === 'integer' ? 'number' : ($settingForm['type'] === 'float' ? 'number' : 'text') }}" 
                                               wire:model="settingForm.value" 
                                               @if($settingForm['type'] === 'float') step="0.01" @endif
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    @endif
                                    @error('settingForm.value') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex items-center space-x-6">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settingForm.is_public"
                                               class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                        <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Public (accessible by frontend)</span>
                                    </label>

                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settingForm.is_encrypted"
                                               class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                        <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Encrypted</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $settingEditMode ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeSettingModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Schedule Event Type Modal --}}
    @if($showEventTypeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEventTypeModal"></div>
                
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <form wire:submit="saveEventType">
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $eventTypeEditMode ? 'Edit Schedule Event Type' : 'Create Schedule Event Type' }}
                                </h3>
                                <button type="button" wire:click="closeEventTypeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Label *</label>
                                        <input type="text" wire:model="eventTypeForm.label" maxlength="50" placeholder="e.g. Office Support, Project Remote"
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @error('eventTypeForm.label') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                        <textarea wire:model="eventTypeForm.description" rows="2" placeholder="Optional description of the event type"
                                                  class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                        @error('eventTypeForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color *</label>
                                    <div class="grid grid-cols-4 md:grid-cols-6 gap-3">
                                        @php
                                        $colorOptions = [
                                            'bg-blue-500' => 'Blue',
                                            'bg-green-500' => 'Green', 
                                            'bg-red-500' => 'Red',
                                            'bg-yellow-500' => 'Yellow',
                                            'bg-purple-500' => 'Purple',
                                            'bg-pink-500' => 'Pink',
                                            'bg-indigo-500' => 'Indigo',
                                            'bg-orange-500' => 'Orange',
                                            'bg-teal-500' => 'Teal',
                                            'bg-cyan-500' => 'Cyan',
                                            'bg-gray-500' => 'Gray',
                                            'bg-slate-500' => 'Slate'
                                        ];
                                        @endphp
                                        
                                        @foreach($colorOptions as $color => $name)
                                        <label class="flex flex-col items-center cursor-pointer">
                                            <input type="radio" wire:model="eventTypeForm.color" value="{{ $color }}" class="sr-only">
                                            <div class="w-8 h-8 rounded-full {{ $color }} border-2 {{ $eventTypeForm['color'] === $color ? 'border-neutral-800 dark:border-neutral-200 ring-2 ring-offset-1 ring-sky-500' : 'border-neutral-300 dark:border-neutral-600' }} transition-all"></div>
                                            <span class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">{{ $name }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @error('eventTypeForm.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Tailwind Classes *</label>
                                    <input type="text" wire:model="eventTypeForm.tailwind_classes" placeholder="e.g. bg-blue-500 text-white border-blue-600"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">CSS classes used to style the event type badge (background, text color, border)</p>
                                    @error('eventTypeForm.tailwind_classes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                                        <input type="number" wire:model="eventTypeForm.sort_order" min="0"
                                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @error('eventTypeForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex items-center justify-center">
                                        <label class="flex items-center">
                                            <input type="checkbox" wire:model="eventTypeForm.is_active"
                                                   class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500">
                                            <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ $eventTypeEditMode ? 'Update' : 'Create' }}
                            </button>
                            <button type="button" wire:click="closeEventTypeModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>