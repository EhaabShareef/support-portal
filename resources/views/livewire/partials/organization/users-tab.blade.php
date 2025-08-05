<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Users</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Manage users belonging to this organization</p>
        </div>
        
        @can('users.create')
        <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            Add User
        </button>
        @endcan
    </div>

    {{-- Users List --}}
    @if($organization->users->count() > 0)
        <div class="space-y-3">
            @foreach($organization->users as $user)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 hover:bg-white/10 transition-all duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                        {{ $user->name }}
                                    </h4>
                                    @if($user->hasRole('Super Admin'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                            Super Admin
                                        </span>
                                    @elseif($user->hasRole('Admin'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                            Admin
                                        </span>
                                    @elseif($user->hasRole('Agent'))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                            Agent
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                                            Client
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 mt-1 text-xs text-neutral-600 dark:text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-envelope class="h-3 w-3" />
                                        {{ $user->email }}
                                    </span>
                                    @if($user->department)
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-building-office class="h-3 w-3" />
                                        {{ $user->department->name }}
                                    </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        Joined {{ $user->created_at->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            
                            @can('users.view')
                            <button class="inline-flex items-center px-3 py-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                                <x-heroicon-o-eye class="h-3 w-3 mr-1" />
                                View
                            </button>
                            @endcan
                            
                            @can('users.edit')
                            <button class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                <x-heroicon-o-pencil class="h-3 w-3 mr-1" />
                                Edit
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-users class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No users found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any users yet.</p>
            @can('users.create')
            <div class="mt-6">
                <button class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First User
                </button>
            </div>
            @endcan
        </div>
    @endif
</div>