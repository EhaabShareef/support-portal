<div class="space-y-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">User Settings</h1>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage department groups and departments for user access control</p>
        </div>
        <a href="{{ route('settings') }}" 
           class="inline-flex items-center px-4 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            <x-heroicon-o-arrow-left class="h-4 w-4 mr-2" />
            Back to Settings
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg">
            <div class="flex items-center">
                <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                {{ session('message') }}
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Department Groups Section --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Department Groups</h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Create and manage department groups for user access control</p>
                </div>
                <button wire:click="$set('showCreateGroupForm', true)" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Department Group
                </button>
            </div>
        </div>

        {{-- Search --}}
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                <input type="text" wire:model.live="searchGroups" 
                       placeholder="Search department groups..."
                       class="pl-10 pr-4 py-2 w-full text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 focus:ring-blue-500 focus:border-blue-500 dark:text-neutral-100">
            </div>
        </div>

        {{-- Department Groups Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Departments</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($departmentGroups as $group)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full" style="background-color: {{ $group->color }}"></div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $group->name }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Order: {{ $group->sort_order }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $group->description ?: 'No description' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $group->departments_count }} departments
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ $group->users_count }} users
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $group->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $group->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="editDepartmentGroup({{ $group->id }})" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <x-heroicon-o-pencil class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleDepartmentGroupStatus({{ $group->id }})" 
                                            class="text-{{ $group->is_active ? 'red' : 'green' }}-600 hover:text-{{ $group->is_active ? 'red' : 'green' }}-900 dark:text-{{ $group->is_active ? 'red' : 'green' }}-400 dark:hover:text-{{ $group->is_active ? 'red' : 'green' }}-300">
                                        <x-heroicon-o-{{ $group->is_active ? 'pause' : 'play' }} class="h-4 w-4" />
                                    </button>
                                    <button wire:click="deleteDepartmentGroup({{ $group->id }})" 
                                            onclick="return confirm('Are you sure you want to delete this department group?')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                No department groups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">
            {{ $departmentGroups->links() }}
        </div>
    </div>

    {{-- Departments Section --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">Departments</h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">Create and manage departments. Departments can belong to multiple groups for overlapping access.</p>
                </div>
                <button wire:click="$set('showCreateDepartmentForm', true)" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Department
                </button>
            </div>
        </div>

        {{-- Search and Filters --}}
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                    <input type="text" wire:model.live="searchDepartments" 
                           placeholder="Search departments..."
                           class="pl-10 pr-4 py-2 w-full text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 focus:ring-blue-500 focus:border-blue-500 dark:text-neutral-100">
                </div>
                <div>
                    <select wire:model.live="filterGroup" 
                            class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-lg bg-white dark:bg-neutral-700 focus:ring-blue-500 focus:border-blue-500 dark:text-neutral-100">
                        <option value="">All Department Groups</option>
                        @foreach($allDepartmentGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Departments Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Department Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($departments as $department)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8 rounded-full bg-neutral-200 dark:bg-neutral-600 flex items-center justify-center">
                                        <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ substr($department->name, 0, 2) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $department->name }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Order: {{ $department->sort_order }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $department->description ?: 'No description' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                @if($department->departmentGroup)
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-4 w-4 rounded-full mr-2" style="background-color: {{ $department->departmentGroup->color }}"></div>
                                        {{ $department->departmentGroup->name }}
                                    </div>
                                @else
                                    <span class="text-neutral-500 dark:text-neutral-400">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $department->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="editDepartment({{ $department->id }})" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <x-heroicon-o-pencil class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleDepartmentStatus({{ $department->id }})" 
                                            class="text-{{ $department->is_active ? 'red' : 'green' }}-600 hover:text-{{ $department->is_active ? 'red' : 'green' }}-900 dark:text-{{ $department->is_active ? 'red' : 'green' }}-400 dark:hover:text-{{ $department->is_active ? 'red' : 'green' }}-300">
                                        <x-heroicon-o-{{ $department->is_active ? 'pause' : 'play' }} class="h-4 w-4" />
                                    </button>
                                    <button wire:click="deleteDepartment({{ $department->id }})" 
                                            onclick="return confirm('Are you sure you want to delete this department?')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                No departments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">
            {{ $departments->links() }}
        </div>
    </div>

    {{-- Create Department Group Modal --}}
    @if($showCreateGroupForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Create Department Group</h3>
                
                <form wire:submit="createDepartmentGroup">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="groupName" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('groupName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea wire:model="groupDescription" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100"></textarea>
                            @error('groupDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                            <input type="color" wire:model="groupColor" 
                                   class="w-full h-10 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            @error('groupColor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                            <input type="number" wire:model="groupSortOrder" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('groupSortOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="groupIsActive" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showCreateGroupForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Create Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Department Group Modal --}}
    @if($showEditGroupForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Edit Department Group</h3>
                
                <form wire:submit="updateDepartmentGroup">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="groupName" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('groupName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea wire:model="groupDescription" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100"></textarea>
                            @error('groupDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color</label>
                            <input type="color" wire:model="groupColor" 
                                   class="w-full h-10 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            @error('groupColor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                            <input type="number" wire:model="groupSortOrder" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('groupSortOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="groupIsActive" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showEditGroupForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Update Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Create Department Modal --}}
    @if($showCreateDepartmentForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Create Department</h3>
                
                <form wire:submit="createDepartment">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="departmentName" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('departmentName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea wire:model="departmentDescription" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100"></textarea>
                            @error('departmentDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group (Optional)</label>
                            <select wire:model="departmentGroupId" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Department Group (Optional)</option>
                                @foreach($allDepartmentGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Leave empty to create a department without group assignment</p>
                            @error('departmentGroupId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                            <input type="number" wire:model="departmentSortOrder" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('departmentSortOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="departmentIsActive" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showCreateDepartmentForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Create Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit Department Modal --}}
    @if($showEditDepartmentForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Edit Department</h3>
                
                <form wire:submit="updateDepartment">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="departmentName" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('departmentName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                            <textarea wire:model="departmentDescription" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100"></textarea>
                            @error('departmentDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group (Optional)</label>
                            <select wire:model="departmentGroupId" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Department Group (Optional)</option>
                                @foreach($allDepartmentGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Leave empty to remove group assignment</p>
                            @error('departmentGroupId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Sort Order</label>
                            <input type="number" wire:model="departmentSortOrder" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('departmentSortOrder') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="departmentIsActive" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showEditDepartmentForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Update Department
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
