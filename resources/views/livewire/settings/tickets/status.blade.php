<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-flag class="h-8 w-8" />
                    Ticket Status Management
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Create and manage ticket statuses with custom colors and department group assignments</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="createStatus" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    New Status
                </button>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if($showFlash)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
             class="p-4 rounded-lg shadow {{ $flashType === 'success' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' : 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200' }}">
            <div class="flex items-center">
                @if($flashType === 'success')
                    <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                @else
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                @endif
                <span>{{ $flashMessage }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Status List --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Status Overview --}}
            <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-8 w-8 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex items-center justify-center">
                        <x-heroicon-o-list-bullet class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Status Overview</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">Manage all ticket statuses and their properties</p>
                    </div>
                </div>
                
                @if($statuses->count() > 0)
                    <div class="space-y-3">
                        @foreach($statuses as $status)
                            @php
                                $statusColors = ['bg' => $status->color, 'text' => $this->ticketColorService->getContrastColor($status->color)];
                                $colorDetails = $this->getColorDetails($statusColors);
                            @endphp
                            
                            <div class="flex items-center justify-between p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-all duration-200">
                                <div class="flex items-center gap-3">
                                    {{-- Status Badge --}}
                                    <div class="h-10 w-10 rounded-lg flex items-center justify-center" style="{{ $colorDetails['bg'] }} {{ $colorDetails['text'] }}">
                                        <x-heroicon-o-flag class="h-5 w-5" />
                                    </div>
                                    
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-medium text-neutral-800 dark:text-neutral-100">{{ $status->name }}</h4>
                                            @if($status->is_protected)
                                                <span class="px-2 py-1 text-xs bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 rounded-full">Protected</span>
                                            @endif
                                            @if(!$status->is_active)
                                                <span class="px-2 py-1 text-xs bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 rounded-full">Inactive</span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $status->description ?: 'No description' }}</p>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Key: {{ $status->key }} | Order: {{ $status->sort_order }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    {{-- Department Groups --}}
                                    <div class="flex items-center gap-1">
                                        @if($status->departmentGroups->count() > 0)
                                            <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $status->departmentGroups->count() }} groups</span>
                                        @else
                                            <span class="text-xs text-neutral-400 dark:text-neutral-500">No groups</span>
                                        @endif
                                    </div>
                                    
                                    {{-- Action Buttons --}}
                                    <div class="flex items-center gap-1">
                                        <button wire:click="editStatus({{ $status->id }})" 
                                                class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200"
                                                title="Edit Status">
                                            <x-heroicon-o-pencil class="h-4 w-4" />
                                        </button>
                                        <button wire:click="assignStatusToGroups({{ $status->id }})" 
                                                class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200"
                                                title="Assign to Groups">
                                            <x-heroicon-o-user-group class="h-4 w-4" />
                                        </button>
                                        <button wire:click="toggleStatusActive({{ $status->id }})" 
                                                class="p-1 rounded-md hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 transition-colors duration-200"
                                                title="{{ $status->is_active ? 'Deactivate' : 'Activate' }}">
                                            @if($status->is_active)
                                                <x-heroicon-o-eye class="h-4 w-4" />
                                            @else
                                                <x-heroicon-o-eye-slash class="h-4 w-4" />
                                            @endif
                                        </button>
                                        @if(!$status->is_protected)
                                            <button wire:click="deleteStatus({{ $status->id }})" 
                                                    wire:confirm="Are you sure you want to delete this status? This action cannot be undone."
                                                    class="p-1 rounded-md hover:bg-red-100 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200 transition-colors duration-200"
                                                    title="Delete Status">
                                                <x-heroicon-o-trash class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-flag class="h-12 w-12 text-neutral-400 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 mb-2">No Statuses Found</h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">Create your first ticket status to get started</p>
                        <button wire:click="createStatus" 
                                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                            Create First Status
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Forms Panel --}}
        <div class="space-y-6">
            @if($showCreateForm || $showEditForm)
                {{-- Create/Edit Status Form --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-8 w-8 bg-sky-100 dark:bg-sky-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-pencil class="h-4 w-4 text-sky-600 dark:text-sky-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                {{ $showEditForm ? 'Edit Status' : 'Create New Status' }}
                            </h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $showEditForm ? 'Update status properties and colors' : 'Define a new ticket status' }}
                            </p>
                        </div>
                    </div>
                    
                    @if($showEditForm && $editingStatus && $editingStatus->is_protected)
                        <div class="mb-4 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-shield-check class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                                <span class="text-sm font-medium text-purple-800 dark:text-purple-200">Protected Status</span>
                            </div>
                            <p class="text-sm text-purple-700 dark:text-purple-300 mt-1">
                                This is a protected status. Only colors and department group assignments can be modified.
                            </p>
                        </div>
                    @endif
                    
                    <form wire:submit.prevent="saveStatus" class="space-y-4">
                        {{-- Status Name --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status Name</label>
                            <input type="text" wire:model="statusName" wire:blur="generateStatusKey"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                   placeholder="e.g., In Progress"
                                   {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}>
                            @error('statusName')
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Key --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Status Key</label>
                            <input type="text" wire:model="statusKey"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                   placeholder="e.g., in_progress"
                                   {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}>
                            @error('statusKey')
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
                            <textarea wire:model="statusDescription" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                      placeholder="Optional description for this status"
                                      {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}></textarea>
                            @error('statusDescription')
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="sortOrder" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                   placeholder="0"
                                   {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}>
                            @error('sortOrder')
                                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status Properties --}}
                        <div class="space-y-3">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Status Properties</label>
                            <div class="space-y-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="isActive" class="h-4 w-4 text-sky-600 border-neutral-300 rounded focus:ring-sky-500 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                           {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}>
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                                </label>
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="isProtected" class="h-4 w-4 text-sky-600 border-neutral-300 rounded focus:ring-sky-500 {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'opacity-50 cursor-not-allowed' : '' }}"
                                           {{ $showEditForm && $editingStatus && $editingStatus->is_protected ? 'disabled' : '' }}>
                                    <span class="text-sm text-neutral-700 dark:text-neutral-300">Protected (cannot be deleted/edited)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Color Preview --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Color Preview</label>
                            <div class="flex items-center gap-3 p-4 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800/50">
                                @php
                                    $previewColors = ['bg' => $statusColor['bg'], 'text' => $statusColor['text']];
                                    $previewColorDetails = $this->getColorDetails($previewColors);
                                @endphp
                                
                                <div class="h-8 w-8 rounded-lg flex items-center justify-center" style="{{ $previewColorDetails['bg'] }} {{ $previewColorDetails['text'] }}">
                                    <x-heroicon-o-flag class="h-4 w-4" />
                                </div>
                                
                                <span class="px-3 py-1 rounded-full text-sm font-medium" style="{{ $previewColorDetails['bg'] }} {{ $previewColorDetails['text'] }}">
                                    {{ $statusName ?: 'Status Name' }}
                                </span>
                            </div>
                        </div>

                        {{-- Background Color Picker --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Background Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" 
                                       wire:model="statusColor.bg" 
                                       wire:change="setCustomBgColor($event.target.value)"
                                       class="h-10 w-16 rounded-lg border border-neutral-300 dark:border-neutral-600 cursor-pointer"
                                       value="{{ $statusColor['bg'] }}">
                                <input type="text" 
                                       wire:model="statusColor.bg" 
                                       wire:change="setCustomBgColor($event.target.value)"
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200"
                                       placeholder="#f3f4f6">
                            </div>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Text color will be automatically calculated for optimal contrast</p>
                        </div>

                        {{-- Quick Color Presets --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Quick Color Presets</label>
                            <div class="grid grid-cols-3 gap-3">
                                @php
                                    $colorPresets = [
                                        ['bg' => '#f3f4f6', 'name' => 'Gray'],
                                        ['bg' => '#dbeafe', 'name' => 'Blue'],
                                        ['bg' => '#fed7aa', 'name' => 'Orange'],
                                        ['bg' => '#fecaca', 'name' => 'Red'],
                                        ['bg' => '#dcfce7', 'name' => 'Green'],
                                        ['bg' => '#fef3c7', 'name' => 'Yellow'],
                                        ['bg' => '#f3e8ff', 'name' => 'Purple'],
                                        ['bg' => '#fce7f3', 'name' => 'Pink'],
                                        ['bg' => '#ecfdf5', 'name' => 'Emerald'],
                                    ];
                                @endphp
                                @foreach($colorPresets as $preset)
                                    @php
                                        $presetTextColor = $this->ticketColorService->getContrastColor($preset['bg']);
                                    @endphp
                                    <button type="button" 
                                            wire:click="setCustomBgColor('{{ $preset['bg'] }}')"
                                            class="relative p-3 rounded-lg border-2 transition-all duration-200 hover:scale-105 {{ $statusColor['bg'] === $preset['bg'] ? 'border-sky-500 ring-2 ring-sky-200 dark:ring-sky-800' : 'border-neutral-200 dark:border-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600' }}">
                                        
                                        <div class="h-8 w-8 rounded-lg mx-auto flex items-center justify-center" style="background-color: {{ $preset['bg'] }};">
                                            <span class="text-xs font-bold" style="color: {{ $presetTextColor }};">Aa</span>
                                        </div>
                                        
                                        <div class="mt-2 text-center">
                                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $preset['name'] }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-3 pt-4">
                            <button type="submit" 
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                                <x-heroicon-o-check class="h-4 w-4 mr-2" />
                                {{ $showEditForm ? 'Update Status' : 'Create Status' }}
                            </button>
                            <button type="button" 
                                    wire:click="$set('showCreateForm', false); $set('showEditForm', false)"
                                    class="inline-flex items-center px-3 py-2 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-md transition-all duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            @if($showAssignForm && $selectedStatusForAssignment)
                {{-- Department Group Assignment Form --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="h-8 w-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-user-group class="h-4 w-4 text-green-600 dark:text-green-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Assign to Groups</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Select which department groups can use this status</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                            Status: <span class="font-semibold">{{ $selectedStatusForAssignment->name }}</span>
                        </h4>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach($departmentGroups as $group)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 cursor-pointer">
                                <input type="checkbox" 
                                       wire:model="assignedDepartmentGroups" 
                                       value="{{ $group->id }}"
                                       class="h-4 w-4 text-sky-600 border-neutral-300 rounded focus:ring-sky-500">
                                <div>
                                    <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $group->name }}</span>
                                    @if($group->description)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $group->description }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3 pt-4">
                        <button type="button" 
                                wire:click="saveAssignments"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                            <x-heroicon-o-check class="h-4 w-4 mr-2" />
                            Save Assignments
                        </button>
                        <button type="button" 
                                wire:click="$set('showAssignForm', false)"
                                class="inline-flex items-center px-3 py-2 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 text-neutral-700 dark:text-neutral-300 text-sm font-medium rounded-md transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            @endif

            @if(!$showCreateForm && !$showEditForm && !$showAssignForm)
                {{-- Instructions Panel --}}
                <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-md border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="h-8 w-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center">
                            <x-heroicon-o-information-circle class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">How to Manage Statuses</h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">Follow these steps to manage ticket statuses</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">1</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Create Status</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Click "New Status" to create a new ticket status</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">2</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Customize Colors</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Choose background and text colors for visual identification</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start gap-3">
                            <div class="h-6 w-6 bg-sky-100 dark:bg-sky-900/40 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-xs font-bold text-sky-600 dark:text-sky-400">3</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Assign to Groups</h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">Control which department groups can use each status</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
