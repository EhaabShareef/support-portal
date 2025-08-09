<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-shield-check class="h-8 w-8" />
                    Role Management
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage system roles and permissions</p>
            </div>

            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                New Role
            </button>
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

    {{-- Search --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md">
        <div class="grid grid-cols-1 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Search Roles</label>
                <input wire:model.live="search" type="text" id="search" placeholder="Search by role name or description..."
                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 dark:focus:ring-sky-400 dark:focus:border-sky-400 transition-colors">
            </div>
        </div>
    </div>

    <div class="flex flex-col space-y-6" x-data="{ showForm: @entangle('showModal') }">
        
        {{-- Inline Edit Form --}}
        <div x-show="showForm" 
             x-transition:enter="transition ease-out duration-400" 
             x-transition:enter-start="opacity-0 transform -translate-y-2 scale-98" 
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 transform -translate-y-2 scale-98"
             class="bg-white/60 dark:bg-neutral-800/60 backdrop-blur border border-neutral-200 dark:border-neutral-700 
                    rounded-lg shadow-lg space-y-6"
             style="display: none;"
             x-cloak>

            <form wire:submit="saveRole" class="flex flex-col">
                {{-- Header --}}
                <div class="bg-white dark:bg-neutral-800 px-6 pt-6 pb-4 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl leading-6 font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ $editMode ? 'Edit Role' : 'Create New Role' }}
                        </h3>
                        <button type="button" wire:click="closeModal" 
                            class="rounded-md text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-sky-500 transition-colors">
                            <span class="sr-only">Close</span>
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                {{-- Scrollable Content --}}
                <div class="bg-white dark:bg-neutral-800 px-6 py-4 overflow-y-auto max-h-[70vh]">
                    <div class="w-full space-y-6">
                                    
                                {{-- Admin Role Warning Banner --}}
                                @if($this->isEditingAdminRole)
                                    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-400" />
                                            </div>
                                            <div class="ml-3">
                                                <h4 class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                                    Admin Role Protection
                                                </h4>
                                                <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                                                    The Admin role is protected and cannot be modified for security reasons. 
                                                    Most fields are disabled to prevent accidental changes to critical system permissions.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                        {{-- Basic Role Information --}}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="showForm" x-transition.delay.100ms>
                                            <div class="form-field-stagger">
                                                <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Role Name</label>
                                                <input wire:model="form.name" type="text" id="name" 
                                                    class="mt-1 block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-white/60 dark:bg-neutral-700/60 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all duration-200"
                                                    {{ $editMode && $form['name'] === 'admin' ? 'disabled' : '' }}>
                                                @error('form.name') <span class="text-red-500 text-sm animate-pulse">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="form-field-stagger">
                                                <label for="guard_name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Guard</label>
                                                <select wire:model="form.guard_name" id="guard_name" 
                                                    class="mt-1 block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-white/60 dark:bg-neutral-700/60 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all duration-200"
                                                    {{ $editMode ? 'disabled' : '' }}>
                                                    <option value="web">Web</option>
                                                </select>
                                                @error('form.guard_name') <span class="text-red-500 text-sm animate-pulse">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div class="form-field-stagger" x-show="showForm" x-transition.delay.150ms>
                                            <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Description</label>
                                            <textarea wire:model="form.description" id="description" rows="3" 
                                                class="mt-1 block w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-white/60 dark:bg-neutral-700/60 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-transparent transition-all duration-200"
                                                placeholder="Brief description of this role's purpose..."
                                                {{ $this->isEditingAdminRole ? 'disabled' : '' }}></textarea>
                                            @error('form.description') <span class="text-red-500 text-sm animate-pulse">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Improved Permission Grid --}}
                                        @if($editMode && $form['name'] !== 'admin')
                                            <div>
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Permissions</h4>
                                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                                        {{ count($selectedPermissions) }} permissions selected
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-6">
                                                    @foreach($permissionMatrix as $groupKey => $group)
                                                        <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg">
                                                            {{-- Group Header --}}
                                                            <div class="bg-neutral-50 dark:bg-neutral-700/50 px-4 py-3 border-b border-neutral-200 dark:border-neutral-600">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-center space-x-3">
                                                                        @if(isset($group['icon']))
                                                                            <div class="flex-shrink-0">
                                                                                <x-dynamic-component :component="$group['icon']" class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                                                                            </div>
                                                                        @endif
                                                                        <div>
                                                                            <h5 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ $group['label'] }}</h5>
                                                                            @if(isset($group['description']))
                                                                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $group['description'] }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            {{-- Group Content --}}
                                                            <div class="p-4">
                                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                                    @foreach($group['modules'] as $moduleKey => $module)
                                                                        <div class="bg-white dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-600 p-4">
                                                                            <div class="flex items-center justify-between mb-3">
                                                                                <div class="flex items-center space-x-2">
                                                                                    @if(isset($module['icon']))
                                                                                        <x-dynamic-component :component="$module['icon']" class="h-4 w-4 text-neutral-500 dark:text-neutral-400" />
                                                                                    @endif
                                                                                    <h6 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $module['label'] }}</h6>
                                                                                </div>
                                                                                <button type="button" wire:click="toggleAllPermissionsForModule('{{ $moduleKey }}')"
                                                                                    class="text-xs px-2 py-1 rounded-md font-medium transition-colors
                                                                                        {{ $this->isModuleFullySelected($moduleKey) 
                                                                                            ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50' 
                                                                                            : 'bg-sky-100 text-sky-700 hover:bg-sky-200 dark:bg-sky-900/30 dark:text-sky-400 dark:hover:bg-sky-900/50' }}">
                                                                                    {{ $this->isModuleFullySelected($moduleKey) ? 'Deselect All' : 'Select All' }}
                                                                                </button>
                                                                            </div>
                                                                            
                                                                            @if(isset($module['description']))
                                                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-3">{{ $module['description'] }}</p>
                                                                            @endif
                                                                            
                                                                            <div class="grid grid-cols-2 gap-2">
                                                                                @foreach($module['actions'] as $actionKey => $action)
                                                                                    <label class="flex items-center space-x-2 p-2 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700/50 cursor-pointer transition-colors">
                                                                                        <input type="checkbox" 
                                                                                               wire:click="togglePermission('{{ $action['permission'] }}')"
                                                                                               {{ in_array($action['permission'], $selectedPermissions) ? 'checked' : '' }}
                                                                                               class="rounded border-neutral-300 dark:border-neutral-500 text-sky-600 focus:ring-sky-500 dark:focus:ring-sky-400 focus:ring-offset-0">
                                                                                        <span class="text-sm text-neutral-700 dark:text-neutral-300 select-none">{{ $action['label'] }}</span>
                                                                                    </label>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @elseif(!$editMode)
                                            <div>
                                                <div class="flex items-center justify-between mb-4">
                                                    <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Permissions</h4>
                                                    <div class="text-sm text-neutral-500 dark:text-neutral-400">
                                                        {{ count($selectedPermissions) }} permissions selected
                                                    </div>
                                                </div>
                                                
                                                <div class="space-y-6">
                                                    @foreach($permissionMatrix as $groupKey => $group)
                                                        <div class="border border-neutral-200 dark:border-neutral-600 rounded-lg">
                                                            {{-- Group Header --}}
                                                            <div class="bg-neutral-50 dark:bg-neutral-700/50 px-4 py-3 border-b border-neutral-200 dark:border-neutral-600">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-center space-x-3">
                                                                        @if(isset($group['icon']))
                                                                            <div class="flex-shrink-0">
                                                                                <x-dynamic-component :component="$group['icon']" class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                                                                            </div>
                                                                        @endif
                                                                        <div>
                                                                            <h5 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">{{ $group['label'] }}</h5>
                                                                            @if(isset($group['description']))
                                                                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $group['description'] }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            {{-- Group Content --}}
                                                            <div class="p-4">
                                                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                                                    @foreach($group['modules'] as $moduleKey => $module)
                                                                        <div class="bg-white dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-600 p-4">
                                                                            <div class="flex items-center justify-between mb-3">
                                                                                <div class="flex items-center space-x-2">
                                                                                    @if(isset($module['icon']))
                                                                                        <x-dynamic-component :component="$module['icon']" class="h-4 w-4 text-neutral-500 dark:text-neutral-400" />
                                                                                    @endif
                                                                                    <h6 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $module['label'] }}</h6>
                                                                                </div>
                                                                                <button type="button" wire:click="toggleAllPermissionsForModule('{{ $moduleKey }}')"
                                                                                    class="text-xs px-2 py-1 rounded-md font-medium transition-colors
                                                                                        {{ $this->isModuleFullySelected($moduleKey) 
                                                                                            ? 'bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50' 
                                                                                            : 'bg-sky-100 text-sky-700 hover:bg-sky-200 dark:bg-sky-900/30 dark:text-sky-400 dark:hover:bg-sky-900/50' }}">
                                                                                    {{ $this->isModuleFullySelected($moduleKey) ? 'Deselect All' : 'Select All' }}
                                                                                </button>
                                                                            </div>
                                                                            
                                                                            @if(isset($module['description']))
                                                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-3">{{ $module['description'] }}</p>
                                                                            @endif
                                                                            
                                                                            <div class="grid grid-cols-2 gap-2">
                                                                                @foreach($module['actions'] as $actionKey => $action)
                                                                                    <label class="flex items-center space-x-2 p-2 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700/50 cursor-pointer transition-colors">
                                                                                        <input type="checkbox" 
                                                                                               wire:click="togglePermission('{{ $action['permission'] }}')"
                                                                                               {{ in_array($action['permission'], $selectedPermissions) ? 'checked' : '' }}
                                                                                               class="rounded border-neutral-300 dark:border-neutral-500 text-sky-600 focus:ring-sky-500 dark:focus:ring-sky-400 focus:ring-offset-0">
                                                                                        <span class="text-sm text-neutral-700 dark:text-neutral-300 select-none">{{ $action['label'] }}</span>
                                                                                    </label>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="bg-neutral-50 dark:bg-neutral-700 px-6 py-4 border-t border-neutral-200 dark:border-neutral-600">
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" wire:click="closeModal" 
                            class="px-4 py-2 border border-neutral-300 dark:border-neutral-500 rounded-md text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-all duration-200 transform hover:scale-105">
                            {{ $this->isEditingAdminRole ? 'Close' : 'Cancel' }}
                        </button>
                        @if(!$this->isEditingAdminRole)
                            <button type="submit" 
                                class="px-6 py-2 bg-sky-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                                {{ $editMode ? 'Update Role' : 'Create Role' }}
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Roles Table --}}
        <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                    <thead class="bg-neutral-50 dark:bg-neutral-800/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Users</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Permissions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-neutral-900 divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($roles as $role)
                            <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full {{ $role->name === 'admin' ? 'bg-blue-100 text-blue-600' : ($role->name === 'support' ? 'bg-green-100 text-green-600' : 'bg-neutral-100 text-neutral-600') }} flex items-center justify-center">
                                                @if($role->name === 'admin')
                                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                                                @elseif($role->name === 'support')
                                                    <x-heroicon-o-user-group class="h-4 w-4" />
                                                @else
                                                    <x-heroicon-o-user class="h-4 w-4" />
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $role->name }}</div>
                                            @if(in_array($role->name, ['admin', 'support', 'client']))
                                                <span class="text-xs text-neutral-500 dark:text-neutral-400">System Role</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-neutral-900 dark:text-neutral-100">
                                        {{ $role->description ?: 'No description provided' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-200">
                                        {{ $role->users_count }} {{ Str::plural('user', $role->users_count) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                                        {{ $role->permissions()->count() }} {{ Str::plural('permission', $role->permissions()->count()) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        @if($role->name === 'admin')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                <x-heroicon-o-lock-closed class="h-3 w-3 mr-1" />
                                                Protected
                                            </span>
                                        @else
                                            <button wire:click="openEditModal({{ $role->id }})" 
                                                class="text-sky-600 hover:text-sky-900 dark:text-sky-400 dark:hover:text-sky-300 transition-colors">
                                                <x-heroicon-o-pencil class="h-4 w-4" />
                                            </button>
                                            @if(!in_array($role->name, ['admin', 'support', 'client']))
                                                <button wire:click="confirmDelete({{ $role->id }})" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors">
                                                    <x-heroicon-o-trash class="h-4 w-4" />
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 whitespace-nowrap text-center">
                                    <div class="text-neutral-500 dark:text-neutral-400">
                                        <x-heroicon-o-shield-check class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-500" />
                                        <h3 class="mt-2 text-sm font-medium">No roles found</h3>
                                        <p class="mt-1 text-sm">No roles match your search criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($roles->hasPages())
                <div class="px-6 py-4 bg-neutral-50 dark:bg-neutral-800/50 border-t border-neutral-200 dark:border-neutral-700">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if($confirmingRoleDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            {{-- Background overlay --}}
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true" wire:click="cancelDelete"></div>
            
            {{-- Modal content --}}
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-neutral-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">Delete Role</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        Are you sure you want to delete the role <strong>{{ $roleToDelete?->name }}</strong>? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteRole" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Delete Role
                        </button>
                        <button wire:click="cancelDelete" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>