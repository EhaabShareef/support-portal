<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-users class="h-8 w-8" />
                    User Management
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage system users and their permissions</p>
            </div>

            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                New User
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

    {{-- Search and Filters --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Search users..."
                       class="pl-10 pr-4 py-2 w-full text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
            </div>

            <select wire:model.live="filterRole" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Roles</option>
                @foreach ($availableRoles as $role)
                    <option value="{{ $role }}">{{ $role }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterDepartment" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Departments</option>
                @foreach ($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterOrganization" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Organizations</option>
                @foreach ($organizations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Users List View --}}
    <div class="space-y-4">
        @forelse($users as $user)
            <div wire:key="user-{{ $user->id }}"
                class="glass-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-[1.02]">
                
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    {{-- User Info --}}
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                {{-- Avatar --}}
                                <div class="flex-shrink-0 h-12 w-12">
                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br bg-sky-500/60 flex items-center justify-center shadow-md">
                                        <span class="text-lg font-bold text-white">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </span>
                                    </div>
                                </div>
                                
                                {{-- User Details --}}
                                <div>
                                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        {{ $user->name }}
                                    </h3>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $user->email }}</p>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-500">{{ '@'.$user->username }}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                {{-- Role Badge --}}
                                @php
                                    $role = $user->roles->first();
                                    $roleColors = [
                                        'Super Admin' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        'Admin' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300',
                                        'Agent' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'Client' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    ];
                                @endphp
                                @if($role)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$role->name] ?? 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900/40 dark:text-neutral-300' }}">
                                        {{ $role->name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                                        No Role
                                    </span>
                                @endif
                                
                                {{-- Status Badge --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                    @if($user->is_active)
                                        <x-heroicon-o-check-circle class="h-3 w-3 mr-1" />
                                        Active
                                    @else
                                        <x-heroicon-o-x-circle class="h-3 w-3 mr-1" />
                                        Inactive
                                    @endif
                                </span>
                            </div>
                        </div>

                        {{-- Department/Organization Info --}}
                        <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                            @if($user->department)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-building-office class="h-4 w-4" />
                                    Department: {{ $user->department->name }}
                                </div>
                            @endif
                            @if($user->organization)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-building-office-2 class="h-4 w-4" />
                                    Organization: {{ $user->organization->name }}
                                </div>
                            @endif
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="h-4 w-4" />
                                Joined {{ $user->created_at->format('M d, Y') }}
                            </div>
                            @if($user->last_login_at)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-clock class="h-4 w-4" />
                                    Last seen {{ $user->last_login_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>

                        {{-- Stats --}}
                        <div class="flex flex-wrap items-center gap-6 text-sm">
                            @if($user->hasRole('Client') && $user->organization)
                                <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                    <x-heroicon-o-ticket class="h-4 w-4" />
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $user->tickets_count ?? 0 }}</span> tickets
                                </div>
                            @endif
                            @if($user->hasRole('Agent'))
                                <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                    <x-heroicon-o-clipboard-document-check class="h-4 w-4" />
                                    <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $user->assigned_tickets_count ?? 0 }}</span> assigned
                                </div>
                            @endif
                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-key class="h-4 w-4" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $user->permissions_count ?? 0 }}</span> permissions
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 lg:ml-4">
                        {{-- View --}}
                        <a href="{{ route('admin.users.view', $user) }}"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                            <x-heroicon-o-eye class="h-4 w-4 mr-1" />
                            View
                        </a>

                        {{-- Edit --}}
                        <button wire:click="openEditModal({{ $user->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200"
                            title="Edit">
                            <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                            Edit
                        </button>

                        {{-- Toggle Status --}}
                        <button wire:click="toggleUserStatus({{ $user->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium {{ $user->is_active ? 'text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30' : 'text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30' }} rounded-md transition-all duration-200"
                            title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}">
                            @if($user->is_active)
                                <x-heroicon-o-pause class="h-4 w-4 mr-1" />
                                Deactivate
                            @else
                                <x-heroicon-o-play class="h-4 w-4 mr-1" />
                                Activate
                            @endif
                        </button>

                        {{-- Delete --}}
                        <button wire:click="confirmDelete({{ $user->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200"
                            title="Delete">
                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <x-heroicon-o-users class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No users found</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    @if($search || $filterRole || $filterDepartment || $filterOrganization || $filterStatus !== '')
                        Try adjusting your search criteria.
                    @else
                        Get started by creating your first user.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="flex justify-center pt-4">
        {{ $users->links() }}
    </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" 
                     wire:click="closeModal" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="save">
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center mb-4">
                                <x-heroicon-o-user-plus class="h-6 w-6 text-sky-600 mr-2" />
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $editMode ? 'Edit User' : 'Create New User' }}
                                </h3>
                            </div>

                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label class="form-label">Full Name</label>
                                    <input type="text" wire:model="form.name" class="form-input" />
                                    @error('form.name') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Username --}}
                                <div>
                                    <label class="form-label">Username</label>
                                    <input type="text" wire:model="form.username" class="form-input" />
                                    @error('form.username') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="form.email" class="form-input" />
                                    @error('form.email') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Role --}}
                                <div>
                                    <label class="form-label">Role</label>
                                    <select wire:model="form.role" class="form-select">
                                        <option value="">Select Role</option>
                                        @foreach($availableRoles as $role)
                                            <option value="{{ $role }}">{{ $role }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.role') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Department (for Agents) --}}
                                @if($form['role'] === 'Agent')
                                    <div>
                                        <label class="form-label">Department</label>
                                        <select wire:model="form.department_id" class="form-select">
                                            <option value="">Select Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.department_id') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                @endif

                                {{-- Organization (for Clients) --}}
                                @if($form['role'] === 'Client')
                                    <div>
                                        <label class="form-label">Organization</label>
                                        <select wire:model="form.organization_id" class="form-select">
                                            <option value="">Select Organization</option>
                                            @foreach($organizations as $org)
                                                <option value="{{ $org->id }}">{{ $org->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('form.organization_id') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                @endif

                                {{-- Password --}}
                                <div>
                                    <label class="form-label">
                                        Password 
                                        @if($editMode)
                                            <span class="text-xs text-neutral-500">(leave blank to keep current)</span>
                                        @endif
                                    </label>
                                    <input type="password" wire:model="form.password" class="form-input" />
                                    @error('form.password') <p class="form-error">{{ $message }}</p> @enderror
                                </div>

                                {{-- Confirm Password --}}
                                @if(!$editMode || !empty($form['password']))
                                    <div>
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" wire:model="form.password_confirmation" class="form-input" />
                                        @error('form.password_confirmation') <p class="form-error">{{ $message }}</p> @enderror
                                    </div>
                                @endif

                                {{-- Active Status --}}
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model="form.is_active" id="is_active" 
                                           class="rounded border-neutral-300 text-sky-600 shadow-sm focus:border-sky-300 focus:ring focus:ring-sky-200 focus:ring-opacity-50" />
                                    <label for="is_active" class="ml-2 block text-sm text-neutral-900 dark:text-neutral-100">
                                        Active User
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="btn-primary sm:ml-3">
                                {{ $editMode ? 'Update User' : 'Create User' }}
                            </button>
                            <button type="button" wire:click="closeModal" class="btn-secondary">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($confirmingUserDeletion)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">
                                    Deactivate User
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        Are you sure you want to deactivate this user? This will prevent them from logging in.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteUser" class="btn-danger sm:ml-3">
                            Deactivate
                        </button>
                        <button wire:click="cancelDelete" class="btn-secondary">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>