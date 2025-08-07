<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-ticket class="h-8 w-8" />
                    Support Tickets
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage and track support tickets</p>
            </div>

            @if($this->canCreate)
            <button wire:click="openCreateModal" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                New Ticket
            </button>
            @endif
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

    {{-- Quick Filter Tabs --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md mb-4">
        <div class="flex flex-wrap gap-2">
            <button wire:click="setQuickFilter('all')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $quickFilter === 'all' ? 'bg-sky-600 text-white shadow-md' : 'bg-white/60 dark:bg-neutral-800/50 text-neutral-700 dark:text-neutral-300 hover:bg-sky-100 dark:hover:bg-sky-900/30' }}">
                All Tickets
            </button>
            <button wire:click="setQuickFilter('my_tickets')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $quickFilter === 'my_tickets' ? 'bg-sky-600 text-white shadow-md' : 'bg-white/60 dark:bg-neutral-800/50 text-neutral-700 dark:text-neutral-300 hover:bg-sky-100 dark:hover:bg-sky-900/30' }}">
                My Tickets
            </button>
            @if(auth()->user()->department_id)
            <button wire:click="setQuickFilter('my_department')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $quickFilter === 'my_department' ? 'bg-sky-600 text-white shadow-md' : 'bg-white/60 dark:bg-neutral-800/50 text-neutral-700 dark:text-neutral-300 hover:bg-sky-100 dark:hover:bg-sky-900/30' }}">
                My Department
            </button>
            @endif
            <button wire:click="setQuickFilter('unassigned')" 
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $quickFilter === 'unassigned' ? 'bg-sky-600 text-white shadow-md' : 'bg-white/60 dark:bg-neutral-800/50 text-neutral-700 dark:text-neutral-300 hover:bg-sky-100 dark:hover:bg-sky-900/30' }}">
                Unassigned
            </button>
            
            {{-- Show Closed Toggle --}}
            <div class="ml-auto flex items-center gap-2">
                <label for="showClosed" class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Show Closed:</label>
                <button wire:click="$toggle('showClosed')" 
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $showClosed ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                    <span class="sr-only">Show closed tickets</span>
                    <span aria-hidden="true" 
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $showClosed ? 'translate-x-5' : 'translate-x-0' }}"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    @if($showFilters)
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Search tickets..."
                       class="pl-10 pr-4 py-2 w-full text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
            </div>

            <select wire:model.live="filterStatus" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Statuses</option>
                @foreach ($statusOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterPriority" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Priorities</option>
                @foreach ($priorityOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterType" 
                    class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                <option value="">All Types</option>
                @foreach ($typeOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if(auth()->user()->hasRole('admin'))
                <select wire:model.live="filterOrg" 
                        class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                    <option value="">All Organizations</option>
                    @foreach ($organizations as $org)
                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="filterDept" 
                        class="px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>
    @endif



    {{-- Tickets Table --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md overflow-hidden">
        @if($tickets->count() > 0)
            {{-- Desktop Table Header --}}
            <div class="hidden lg:block bg-neutral-50 dark:bg-neutral-800/50 px-6 py-3 border-b border-neutral-200 dark:border-neutral-700">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">
                    <div class="col-span-2">
                        <button wire:click="sortBy('ticket_number')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Ticket
                            @if($sortBy === 'ticket_number')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-2">
                        <button wire:click="sortBy('subject')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Subject
                            @if($sortBy === 'subject')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-2">Client</div>
                    <div class="col-span-1">
                        <button wire:click="sortBy('priority')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Priority
                            @if($sortBy === 'priority')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-1">
                        <button wire:click="sortBy('status')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Status
                            @if($sortBy === 'status')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-1">Assigned</div>
                    <div class="col-span-1">Last Reply</div>
                    <div class="col-span-1">
                        <button wire:click="sortBy('created_at')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Created
                            @if($sortBy === 'created_at')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-1">Actions</div>
                </div>
            </div>

            {{-- Desktop Table Body --}}
            <div class="hidden lg:block divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($tickets as $ticket)
                    <div wire:key="ticket-{{ $ticket->id }}" class="px-6 py-4 hover:bg-white/10 transition-colors duration-200">
                        <div class="grid grid-cols-12 gap-4 items-center">
                            {{-- Ticket Number --}}
                            <div class="col-span-2">
                                <div class="text-xs font-medium text-sky-600 dark:text-sky-400">
                                    #{{ $ticket->ticket_number }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ ucfirst($ticket->type) }}
                                </div>
                            </div>

                            {{-- Subject --}}
                            <div class="col-span-2">
                                <div class="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                    {{ Str::limit($ticket->subject, 40) }}
                                </div>
                                @if($ticket->description)
                                <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    {{ Str::limit(strip_tags($ticket->description), 60) }}
                                </div>
                                @endif
                            </div>

                            {{-- Client --}}
                            <div class="col-span-2">
                                <div class="text-sm text-neutral-800 dark:text-neutral-200">
                                    {{ Str::limit($ticket->client->name, 20) }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                    {{ Str::limit($ticket->organization->name, 20) }}
                                </div>
                            </div>

                            {{-- Priority --}}
                            <div class="col-span-1">
                                @php
                                    $priorityColors = [
                                        'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                                        'normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? $priorityColors['normal'] }}">
                                    {{ $ticket->priority_label }}
                                </span>
                            </div>

                            {{-- Status --}}
                            <div class="col-span-1">
                                @php
                                    $statusColors = [
                                        'open' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'awaiting_customer_response' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                                        'on_hold' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? $statusColors['open'] }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </div>

                            {{-- Assigned --}}
                            <div class="col-span-1">
                                @if($ticket->assigned)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">{{ substr($ticket->assigned->name, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm text-neutral-800 dark:text-neutral-200">
                                            {{ Str::limit($ticket->assigned->name, 15) }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Unassigned</span>
                                @endif
                            </div>

                            {{-- Last Reply --}}
                            <div class="col-span-1">
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    @if($ticket->messages->count() > 0)
                                        {{ $ticket->messages->first()->created_at->diffForHumans() }}
                                    @else
                                        No replies
                                    @endif
                                </div>
                            </div>

                            {{-- Created --}}
                            <div class="col-span-1">
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    {{ $ticket->created_at->diffForHumans() }}
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="col-span-1">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('tickets.show', $ticket) }}" 
                                       class="inline-flex items-center px-2 py-1 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded transition-all duration-200">
                                        <x-heroicon-o-eye class="h-3 w-3" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Mobile Cards --}}
            <div class="lg:hidden divide-y divide-neutral-200 dark:divide-neutral-700">
                @foreach($tickets as $ticket)
                    <div wire:key="mobile-ticket-{{ $ticket->id }}" class="p-4 hover:bg-white/10 transition-colors duration-200">
                        <div class="space-y-3">
                            {{-- Header Row --}}
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-medium text-sky-600 dark:text-sky-400">
                                            #{{ $ticket->ticket_number }}
                                        </span>
                                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                            {{ ucfirst($ticket->type) }}
                                        </span>
                                    </div>
                                    <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200 truncate">
                                        {{ $ticket->subject }}
                                    </h3>
                                </div>
                                <a href="{{ route('tickets.show', $ticket) }}" 
                                   class="ml-2 inline-flex items-center px-2 py-1 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded transition-all duration-200">
                                    <x-heroicon-o-eye class="h-4 w-4" />
                                </a>
                            </div>

                            {{-- Client and Organization --}}
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                <span class="font-medium">{{ $ticket->client->name }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $ticket->organization->name }}</span>
                            </div>

                            {{-- Status and Priority Badges --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                @php
                                    $priorityColors = [
                                        'low' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                                        'normal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                                        'urgent' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                                    ];
                                    $statusColors = [
                                        'open' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                        'awaiting_customer_response' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                        'closed' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300',
                                        'on_hold' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$ticket->status] ?? $statusColors['open'] }}">
                                    {{ $ticket->status_label }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityColors[$ticket->priority] ?? $priorityColors['normal'] }}">
                                    {{ $ticket->priority_label }}
                                </span>
                            </div>

                            {{-- Assignment and Dates --}}
                            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                                <div>
                                    @if($ticket->assigned)
                                        <div class="flex items-center gap-1">
                                            <div class="w-4 h-4 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-xs font-medium">{{ substr($ticket->assigned->name, 0, 1) }}</span>
                                            </div>
                                            <span>{{ $ticket->assigned->name }}</span>
                                        </div>
                                    @else
                                        <span>Unassigned</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div>Created {{ $ticket->created_at->diffForHumans() }}</div>
                                    @if($ticket->messages->count() > 0)
                                        <div>Last reply {{ $ticket->messages->first()->created_at->diffForHumans() }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12">
                <x-heroicon-o-ticket class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No tickets found</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    @if($search || $filterStatus || $filterPriority || $filterType || $filterOrg || $filterDept || $quickFilter !== 'all')
                        Try adjusting your search criteria or filters.
                    @else
                        Get started by creating your first ticket.
                    @endif
                </p>
            </div>
        @endif
    </div>


    {{-- Pagination --}}
    @if($tickets->hasPages())
    <div class="flex justify-center">
        {{ $tickets->links() }}
    </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCreateModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 wire:click="closeModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                 x-show="show" x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <form wire:submit="save">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Create New Ticket
                            </h3>
                            <button type="button" wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                <x-heroicon-o-x-mark class="h-6 w-6" />
                            </button>
                        </div>

                        <div class="space-y-4">
                            {{-- Subject --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subject *</label>
                                <input type="text" wire:model="form.subject" 
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                       placeholder="Brief description of the issue or request">
                                @error('form.subject') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Type *</label>
                                    <select wire:model="form.type" 
                                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @foreach($typeOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                {{-- Priority --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Priority *</label>
                                    <select wire:model="form.priority" 
                                            class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                        @foreach($priorityOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.priority') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- Department --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department *</label>
                                <select wire:model="form.department_id" 
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    <option value="">Select Department</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.department_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Organization (Only for Agents/Admins) --}}
                            @if(!auth()->user()->hasRole('client'))
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization *</label>
                                <select wire:model.live="form.organization_id" 
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    <option value="">Select Organization</option>
                                    @foreach($organizations as $org)
                                        <option value="{{ $org->id }}">{{ $org->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.organization_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Client (Dependent on Organization) --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Client *</label>
                                <select wire:model="form.client_id" 
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                                    <option value="">{{ $form['organization_id'] ? 'Select Client' : 'Select Organization first' }}</option>
                                    @foreach($this->availableClients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('form.client_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            @else
                            {{-- Show organization name for clients (read-only) --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Organization</label>
                                <div class="w-full px-3 py-2 bg-neutral-100 dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-700 dark:text-neutral-300">
                                    {{ auth()->user()->organization->name }}
                                </div>
                            </div>
                            @endif

                            {{-- Description --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                <textarea wire:model="form.description" rows="4"
                                          class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                          placeholder="Provide detailed information about the issue or request..."></textarea>
                                @error('form.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Note about assignment --}}
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                <p class="text-sm text-blue-800 dark:text-blue-200">
                                    <x-heroicon-o-information-circle class="h-4 w-4 inline mr-1" />
                                    New tickets will be created as "Open" and unassigned. An agent will be assigned once the ticket is reviewed.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create Ticket
                        </button>
                        <button type="button" wire:click="closeModal"
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
