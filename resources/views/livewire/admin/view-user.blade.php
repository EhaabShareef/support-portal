<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.users-roles.index', ['tab' => 'users']) }}"
                    class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:underline transition-colors duration-200">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> Back to Users & Roles
                </a>
                <div class="hidden sm:block w-px h-6 bg-neutral-300 dark:bg-neutral-600"></div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100">{{ $user->name }}</h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $user->roles->first()?->name ?? 'No Role' }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                </span>
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
                <button wire:click="setActiveTab('details')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'details' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-user class="h-5 w-5 mr-2 inline" />
                    User Details
                </button>
                <button wire:click="setActiveTab('permissions')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'permissions' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-key class="h-5 w-5 mr-2 inline" />
                    Permissions
                </button>
                @if($user->hasRole('client'))
                <button wire:click="setActiveTab('tickets')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'tickets' ? 'border-sky-500 text-sky-600 dark:text-sky-400' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}">
                    <x-heroicon-o-ticket class="h-5 w-5 mr-2 inline" />
                    Ticket History
                </button>
                @endif
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- User Details Tab --}}
            @if($activeTab === 'details')
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- User Information --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">User Information</h3>
                            <div class="flex items-center gap-2">
                                <button wire:click="refreshUser"
                                    class="inline-flex items-center px-3 py-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                                    <x-heroicon-o-arrow-path class="h-3 w-3 mr-1" /> Refresh
                                </button>
                                @if($this->canEdit)
                                    @if($editMode)
                                        <button wire:click="save"
                                            class="inline-flex items-center px-3 py-1.5 bg-sky-500 text-white hover:bg-sky-600 rounded-md text-xs transition-all duration-200">
                                            <x-heroicon-o-check class="h-3 w-3 mr-1" /> Save
                                        </button>
                                        <button wire:click="cancel"
                                            class="inline-flex items-center px-3 py-1.5 bg-neutral-500 text-white hover:bg-neutral-600 rounded-md text-xs transition-all duration-200">
                                            <x-heroicon-o-x-mark class="h-3 w-3 mr-1" /> Cancel
                                        </button>
                                    @else
                                        <button wire:click="enableEdit"
                                            class="inline-flex items-center px-3 py-1.5 border border-sky-400 text-sky-400 hover:bg-sky-500 hover:text-white rounded-md text-xs transition-all duration-200">
                                            <x-heroicon-o-pencil class="h-3 w-3 mr-1" /> Edit
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <dl class="space-y-4">
                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Name</dt>
                                <dd class="mt-1 text-sm">
                                    @if($editMode)
                                        <input type="text" wire:model.defer="form.name"
                                            class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                                        @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        <span class="text-neutral-800 dark:text-neutral-200">{{ $user->name }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Username</dt>
                                <dd class="mt-1 text-sm">
                                    @if($editMode)
                                        <input type="text" wire:model.defer="form.username"
                                            class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                                        @error('form.username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        <span class="text-neutral-800 dark:text-neutral-200">{{ $user->username }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Email</dt>
                                <dd class="mt-1 text-sm">
                                    @if($editMode)
                                        <input type="email" wire:model.defer="form.email"
                                            class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                                        @error('form.email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    @else
                                        <span class="text-neutral-800 dark:text-neutral-200">{{ $user->email }}</span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Role</dt>
                                <dd class="mt-1 text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $user->roles->first()?->name ?? 'No Role' }}
                                    </span>
                                </dd>
                            </div>

                            @if($user->department)
                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Department</dt>
                                <dd class="mt-1 text-sm">
                                    <span class="text-neutral-800 dark:text-neutral-200">{{ $user->department->name }}</span>
                                </dd>
                            </div>
                            @endif

                            @if($user->organization)
                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Organization</dt>
                                <dd class="mt-1 text-sm">
                                    <a href="{{ route('organizations.show', $user->organization) }}" 
                                       class="text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:underline">
                                        {{ $user->organization->name }}
                                    </a>
                                </dd>
                            </div>
                            @endif

                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Created</dt>
                                <dd class="mt-1 text-sm">
                                    <span class="text-neutral-800 dark:text-neutral-200">{{ $user->created_at->format('M d, Y') }}</span>
                                </dd>
                            </div>

                            @if($user->last_login_at)
                            <div>
                                <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Last Login</dt>
                                <dd class="mt-1 text-sm">
                                    <span class="text-neutral-800 dark:text-neutral-200">{{ $user->last_login_at->diffForHumans() }}</span>
                                </dd>
                            </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Actions Panel --}}
                <div class="space-y-6">
                    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Quick Actions</h3>
                        
                        @if($this->canEdit)
                            <div class="space-y-3">
                                <button wire:click="toggleActive"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border {{ $user->is_active ? 'border-orange-400 text-orange-400 hover:bg-orange-500' : 'border-green-400 text-green-400 hover:bg-green-500' }} hover:text-white rounded-md text-sm transition-all duration-200">
                                    @if($user->is_active)
                                        <x-heroicon-o-pause class="h-4 w-4 mr-2" /> Deactivate User
                                    @else
                                        <x-heroicon-o-play class="h-4 w-4 mr-2" /> Activate User
                                    @endif
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Permissions Tab --}}
            @if($activeTab === 'permissions')
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">User Permissions</h3>
                    <span class="text-sm text-neutral-600 dark:text-neutral-400">
                        Permissions are managed through user roles
                    </span>
                </div>

                @if(count($this->groupedPermissions) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($this->groupedPermissions as $module => $actions)
                    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md">
                        <h4 class="text-md font-medium text-neutral-800 dark:text-neutral-100 mb-3 capitalize flex items-center">
                            <x-heroicon-o-key class="h-5 w-5 mr-2 text-emerald-500" />
                            {{ str_replace('_', ' ', $module) }}
                        </h4>
                        <div class="space-y-2">
                            @foreach($actions as $action)
                            <div class="flex items-center">
                                <x-heroicon-o-check class="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                <span class="text-sm text-neutral-700 dark:text-neutral-300">{{ $action }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-8 shadow-md text-center">
                    <x-heroicon-o-exclamation-triangle class="h-12 w-12 mx-auto text-amber-500 mb-4" />
                    <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 mb-2">No Direct Permissions</h4>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        This user's permissions are inherited from their assigned role: <strong>{{ $user->roles->first()?->name ?? 'No Role' }}</strong>
                    </p>
                </div>
                @endif
            </div>
            @endif

            {{-- Tickets Tab --}}
            @if($activeTab === 'tickets' && $user->hasRole('client'))
            <div class="space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Ticket History</h3>
                    
                    <div class="w-full sm:w-64">
                        <input type="text" wire:model.live.debounce.300ms="ticketSearch" 
                               placeholder="Search tickets..."
                               class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                    </div>
                </div>

                @if($this->ticketHistory->count() > 0)
                <div class="space-y-4">
                    @foreach($this->ticketHistory as $ticket)
                    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md hover:shadow-lg transition-all duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-medium text-sky-600 dark:text-sky-400">
                                        #{{ $ticket->ticket_number }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->status === 'open') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($ticket->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($ticket->status === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->priority === 'low') bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @elseif($ticket->priority === 'normal') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                        @else bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @endif">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </div>
                                <h4 class="text-md font-medium text-neutral-800 dark:text-neutral-100 mb-1">
                                    {{ $ticket->subject }}
                                </h4>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                    Created {{ $ticket->created_at->diffForHumans() }}
                                    @if($ticket->assigned)
                                        • Assigned to {{ $ticket->assigned->name }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('tickets.show', $ticket) }}" 
                               class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                <x-heroicon-o-eye class="h-4 w-4 mr-1" />
                                View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($this->ticketHistory->hasPages())
                <div class="flex justify-center">
                    {{ $this->ticketHistory->links() }}
                </div>
                @endif
                @else
                <div class="text-center py-12">
                    <x-heroicon-o-ticket class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No tickets found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                        @if($ticketSearch)
                            Try adjusting your search criteria.
                        @else
                            No tickets have been created for this user's organization yet.
                        @endif
                    </p>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>