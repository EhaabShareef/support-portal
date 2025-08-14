<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Organization Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage department groups and departments</p>
        </div>
    </div>

    {{-- Department Groups Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Department Groups</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Organize departments into logical groups</p>
                </div>
                <button wire:click="createDeptGroup" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    New Group
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if($this->departmentGroups->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->departmentGroups as $group)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-6 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-4 h-4 rounded-full mr-3" style="background-color: {{ $group->color }}"></div>
                                    <div>
                                        <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">{{ $group->name }}</h4>
                                        @if(!$group->is_active)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button wire:click="editDeptGroup({{ $group->id }})" 
                                        class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                        title="Edit">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    @if($confirmingDeptGroupDelete === $group->id)
                                        <button wire:click="deleteDeptGroup" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors p-1" 
                                            title="Confirm delete">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancelDeptGroupDelete" 
                                            class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors p-1" 
                                            title="Cancel">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button wire:click="confirmDeleteDeptGroup({{ $group->id }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if($group->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ $group->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between pt-4 border-t border-neutral-200 dark:border-neutral-600">
                                <div class="flex items-center text-sm text-neutral-500 dark:text-neutral-400">
                                    <x-heroicon-o-building-office class="h-4 w-4 mr-1" />
                                    {{ $group->departments_count }} departments
                                </div>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Order: {{ $group->sort_order }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-rectangle-group class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No department groups</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Create your first department group to organize departments.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Departments Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Departments</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage individual departments and their settings</p>
                </div>
                <button wire:click="createDept" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    New Department
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if($this->departments->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->departments as $dept)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-6 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                                        {{ $dept->name }}
                                        @if(!$dept->is_active)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </h4>
                                    @if($dept->departmentGroup)
                                        <p class="text-sm text-neutral-500 dark:text-neutral-400 mt-1">{{ $dept->departmentGroup->name }}</p>
                                    @endif
                                    @if($dept->email)
                                        <p class="text-sm text-sky-600 dark:text-sky-400 mt-1">{{ $dept->email }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button wire:click="editDept({{ $dept->id }})" 
                                        class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                        title="Edit">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    @if($confirmingDeptDelete === $dept->id)
                                        <button wire:click="deleteDept" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors p-1" 
                                            title="Confirm delete">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancelDeptDelete" 
                                            class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors p-1" 
                                            title="Cancel">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button wire:click="confirmDeleteDept({{ $dept->id }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if($dept->description)
                                <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ $dept->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between pt-4 border-t border-neutral-200 dark:border-neutral-600 text-sm text-neutral-500 dark:text-neutral-400">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center">
                                        <x-heroicon-o-users class="h-4 w-4 mr-1" />
                                        {{ $dept->users_count }} users
                                    </div>
                                    <div class="flex items-center">
                                        <x-heroicon-o-ticket class="h-4 w-4 mr-1" />
                                        {{ $dept->tickets_count }} tickets
                                    </div>
                                </div>
                                <span class="text-xs">Order: {{ $dept->sort_order }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-building-office class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No departments</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Create your first department to get started.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Department Group Modal --}}
@if($showDeptGroupModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeDeptGroupModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveDeptGroup">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $deptGroupEditMode ? 'Edit Department Group' : 'Add New Department Group' }}
                        </h3>
                        <button type="button" wire:click="closeDeptGroupModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model="deptGroupForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('deptGroupForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
                            <textarea wire:model="deptGroupForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                            @error('deptGroupForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model="deptGroupForm.color" 
                                       class="w-16 h-10 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                <input type="text" wire:model="deptGroupForm.color" 
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono">
                            </div>
                            @error('deptGroupForm.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="deptGroupForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('deptGroupForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Enable this department group</span>
                            </span>
                            <button type="button" wire:click="$toggle('deptGroupForm.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $deptGroupForm['is_active'] ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Active</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $deptGroupForm['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
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

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveDept">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $deptEditMode ? 'Edit Department' : 'Add New Department' }}
                        </h3>
                        <button type="button" wire:click="closeDeptModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model="deptForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('deptForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Department Group</label>
                            <select wire:model="deptForm.department_group_id"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                <option value="">No Group</option>
                                @foreach($this->departmentGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('deptForm.department_group_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Email</label>
                            <input type="email" wire:model="deptForm.email"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('deptForm.email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
                            <textarea wire:model="deptForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"></textarea>
                            @error('deptForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="deptForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('deptForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Enable this department</span>
                            </span>
                            <button type="button" wire:click="$toggle('deptForm.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $deptForm['is_active'] ? 'bg-green-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Active</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $deptForm['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $deptEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeDeptModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif