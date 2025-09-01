<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100">Manage Users</h1>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Create and manage user accounts with department group assignments</p>
        </div>
        <button wire:click="$set('showCreateForm', true)" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            Add User
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Search</label>
                <input type="text" wire:model.live="search" 
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100"
                       placeholder="Search users...">
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Role</label>
                <select wire:model.live="filterRole" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group</label>
                <select wire:model.live="filterDepartmentGroup" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                    <option value="">All Groups</option>
                    @foreach($departmentGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization</label>
                <select wire:model.live="filterOrganization" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                    <option value="">All Organizations</option>
                    @foreach($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                <thead class="bg-neutral-50 dark:bg-neutral-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Department Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Organization</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-500 dark:text-neutral-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-neutral-800 divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        @if($user->avatar)
                                            <img class="h-10 w-10 rounded-full" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                        @else
                                            <div class="h-10 w-10 rounded-full bg-neutral-200 dark:bg-neutral-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">{{ substr($user->name, 0, 2) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $user->name }}</div>
                                        <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $user->email }}</div>
                                        <div class="text-xs text-neutral-400 dark:text-neutral-500">{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $user->departmentGroup?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900 dark:text-neutral-100">
                                {{ $user->organization?->name ?? 'Unassigned' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="editUser({{ $user->id }})" 
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                        <x-heroicon-o-pencil class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleUserStatus({{ $user->id }})" 
                                            class="text-{{ $user->is_active ? 'red' : 'green' }}-600 hover:text-{{ $user->is_active ? 'red' : 'green' }}-900 dark:text-{{ $user->is_active ? 'red' : 'green' }}-400 dark:hover:text-{{ $user->is_active ? 'red' : 'green' }}-300">
                                        <x-heroicon-o-{{ $user->is_active ? 'pause' : 'play' }} class="h-4 w-4" />
                                    </button>
                                    <button wire:click="deleteUser({{ $user->id }})" 
                                            onclick="return confirm('Are you sure you want to delete this user?')"
                                            class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Create User Modal --}}
    @if($showCreateForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Create New User</h3>
                
                <form wire:submit="createUser">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="name" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Username</label>
                            <input type="text" wire:model="username" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password</label>
                            <input type="password" wire:model="password" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group</label>
                            <select wire:model="department_group_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Department Group</option>
                                @foreach($departmentGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('department_group_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization</label>
                            <select wire:model="organization_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Roles</label>
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="roles" value="{{ $role->name }}" 
                                               class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">{{ ucfirst($role->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_active" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showCreateForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Create User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Edit User Modal --}}
    @if($showEditForm)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-neutral-800 rounded-lg p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">Edit User</h3>
                
                <form wire:submit="updateUser">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
                            <input type="text" wire:model="name" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Username</label>
                            <input type="text" wire:model="username" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
                            <input type="email" wire:model="email" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Password (leave blank to keep current)</label>
                            <input type="password" wire:model="password" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Group</label>
                            <select wire:model="department_group_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Department Group</option>
                                @foreach($departmentGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('department_group_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization</label>
                            <select wire:model="organization_id" 
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-neutral-700 dark:text-neutral-100">
                                <option value="">Select Organization</option>
                                @foreach($organizations as $org)
                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                @endforeach
                            </select>
                            @error('organization_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Roles</label>
                            <div class="space-y-2">
                                @foreach($roles as $role)
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="roles" value="{{ $role->name }}" 
                                               class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                        <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">{{ ucfirst($role->name) }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('roles') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" wire:model="is_active" 
                                       class="rounded border-neutral-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" wire:click="$set('showEditForm', false)" 
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 rounded-lg hover:bg-neutral-200 dark:hover:bg-neutral-600">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Flash Messages --}}
    @if(session()->has('message'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif
</div>