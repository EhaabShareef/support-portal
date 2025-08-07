<div class="space-y-4">
    {{-- Header with Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Users</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">Overview of client users for this organization</p>
        </div>
        
        @if($organization->users->filter(fn($user) => $user->hasRole('client'))->count() > 0)
            <a href="{{ route('users.manage', $organization) }}" 
               class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-users class="h-4 w-4 mr-2" />
                Manage Users
            </a>
        @endif
    </div>

    {{-- Info Banner --}}
    <div x-data="{ showGuidelines: true }" 
         x-show="showGuidelines" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 transform -translate-y-2" 
         x-transition:enter-end="opacity-100 transform translate-y-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 transform translate-y-0" 
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
        <div class="flex items-start gap-3">
            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
            <div class="text-sm flex-1">
                <p class="text-blue-800 dark:text-blue-200 font-medium mb-1">User Management Guidelines</p>
                <ul class="text-blue-700 dark:text-blue-300 space-y-1 text-xs">
                    <li>• Only <strong>Client users</strong> can be created, edited, or deleted from this interface</li>
                    <li>• All users created here will automatically belong to <strong>{{ $organization->name }}</strong></li>
                    <li>• System users (Admin, Agent, Super Admin) are managed separately and are displayed for reference only</li>
                    <li>• Users with existing tickets cannot be deleted until tickets are resolved or reassigned</li>
                </ul>
            </div>
            <button @click="showGuidelines = false" 
                    class="flex-shrink-0 p-1 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 hover:bg-blue-100 dark:hover:bg-blue-800/50 rounded transition-colors duration-200">
                <x-heroicon-o-x-mark class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Users List --}}
    @if($organization->users->count() > 0)
        <div class="space-y-2">
            @foreach($organization->users->take(3) as $user)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-3 hover:bg-white/10 transition-all duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-xs">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-medium text-neutral-800 dark:text-neutral-100 truncate">
                                        {{ $user->name }}
                                    </h4>
                                    @if($user->hasRole('client'))
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                                            Client
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                            {{ $user->roles->first()->name ?? 'System' }}
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-envelope class="h-3 w-3" />
                                        {{ $user->email }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <x-heroicon-o-calendar class="h-3 w-3" />
                                        {{ $user->created_at->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1 ml-3 flex-shrink-0">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @can('users.view')
                            <button class="inline-flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded transition-colors duration-200">
                                <x-heroicon-o-eye class="h-3 w-3" />
                            </button>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        {{-- View All Link --}}
        <div class="text-center pt-4">
            <a href="{{ route('users.manage', $organization) }}" 
               class="inline-flex items-center text-sm text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 font-medium">
                <x-heroicon-o-arrow-right class="h-4 w-4 mr-1" />
                View All Users ({{ $organization->users->count() }})
            </a>
        </div>
    @else
        <div class="text-center py-12">
            <x-heroicon-o-users class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No users found</h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">This organization doesn't have any users yet.</p>
            @can('users.create')
            <div class="mt-6">
                <a href="{{ route('users.manage', $organization) }}" 
                   class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add First Client User
                </a>
            </div>
            @endcan
        </div>
    @endif
</div>