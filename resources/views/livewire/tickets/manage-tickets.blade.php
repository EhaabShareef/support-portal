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
            <a href="{{ route('tickets.create') }}" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                New Ticket
            </a>
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
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
        @if($tickets->count() > 0)
            {{-- Desktop Table Header --}}
            <div class="hidden lg:block bg-neutral-50 dark:bg-neutral-800/50 px-6 py-3 border-b border-neutral-200 dark:border-neutral-700">
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">
                    <div class="col-span-1">
                        <button wire:click="sortBy('ticket_number')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Ticket
                            @if($sortBy === 'ticket_number')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-1"></div> {{-- Icons column --}}
                    <div class="col-span-2">
                        <button wire:click="sortBy('subject')" class="flex items-center hover:text-neutral-700 dark:hover:text-neutral-300">
                            Subject
                            @if($sortBy === 'subject')
                                <x-heroicon-o-chevron-{{ $sortDirection === 'asc' ? 'up' : 'down' }} class="h-3 w-3 ml-1" />
                            @endif
                        </button>
                    </div>
                    <div class="col-span-1">Client</div>
                    <div class="col-span-1">Department</div>
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
                    <div class="col-span-1">Owner</div>
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
                            <div class="col-span-1">
                                <a href="{{ route('tickets.show', $ticket) }}" 
                                   class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-200 hover:underline cursor-pointer transition-all duration-200"
                                   title="View Ticket Details">
                                    #{{ $ticket->ticket_number }}
                                </a>
                            </div>

                            {{-- Icons --}}
                            <div class="col-span-1">
                                <div class="flex items-center gap-1">
                                    @if($ticket->internal_note_count)
                                        <x-heroicon-o-document-text class="h-4 w-4 text-neutral-500" title="This ticket has internal notes" />
                                    @endif
                                    @if($ticket->attachments_count)
                                        <x-heroicon-o-paper-clip class="h-4 w-4 text-neutral-500" title="This ticket has attachments" />
                                    @endif
                                </div>
                            </div>

                            {{-- Subject --}}
                            <div class="col-span-2">
                                <div class="text-sm font-medium text-neutral-800 dark:text-neutral-200" title="{{ $ticket->subject }}">
                                    {{ Str::limit($ticket->subject, 50) }}
                                </div>
                            </div>

                            {{-- Client --}}
                            <div class="col-span-1">
                                <div class="text-sm text-neutral-800 dark:text-neutral-200" title="{{ $ticket->client->name }}">
                                    {{ Str::limit($ticket->client->name, 15) }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400" title="{{ $ticket->organization->name }}">
                                    {{ Str::limit($ticket->organization->name, 15) }}
                                </div>
                            </div>

                            {{-- Department --}}
                            <div class="col-span-1">
                                <div class="text-sm text-neutral-800 dark:text-neutral-200" title="{{ $ticket->department?->departmentGroup?->name }}">
                                    {{ Str::limit($ticket->department?->departmentGroup?->name ?? 'N/A', 12) }}
                                </div>
                                <div class="text-xs text-neutral-500 dark:text-neutral-400" title="{{ $ticket->department?->name }}">
                                    {{ Str::limit($ticket->department?->name ?? 'N/A', 12) }}
                                </div>
                            </div>

                            {{-- Priority --}}
                            <div class="col-span-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                                    <x-dynamic-component :component="\App\Enums\TicketPriority::from($ticket->priority)->icon()" class="h-3 w-3 mr-1" />
                                    {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                                </span>
                            </div>

                            {{-- Status --}}
                            <div class="col-span-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="{{ $ticket->getStatusCssClass() }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </div>

                            {{-- Owner --}}
                            <div class="col-span-1">
                                @if($ticket->owner)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                            <span class="text-white text-xs font-medium">{{ substr($ticket->owner->name, 0, 1) }}</span>
                                        </div>
                                        <div class="text-sm text-neutral-800 dark:text-neutral-200">
                                            {{ Str::limit($ticket->owner->name, 15) }}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">Unassigned</span>
                                @endif
                            </div>

                            {{-- Last Reply --}}
                            <div class="col-span-1">
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    @if($ticket->latest_message_at)
                                        {{ $ticket->latest_message_at->diffForHumans() }}
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
                                <x-tickets.table-actions :ticket="$ticket" />
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
                                        <a href="{{ route('tickets.show', $ticket) }}" 
                                           class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-200 hover:underline cursor-pointer transition-all duration-200"
                                           title="View Ticket Details">
                                            #{{ $ticket->ticket_number }}
                                        </a>
                                        @if($ticket->internal_note_count)
                                            <x-heroicon-o-document-text class="h-4 w-4 text-neutral-500" title="This ticket has internal notes" />
                                        @endif
                                        @if($ticket->attachments_count)
                                            <x-heroicon-o-paper-clip class="h-4 w-4 text-neutral-500" title="This ticket has attachments" />
                                        @endif
                                    </div>
                                    <h3 class="text-sm font-medium text-neutral-800 dark:text-neutral-200 truncate" title="{{ $ticket->subject }}">
                                        {{ Str::limit($ticket->subject, 50) }}
                                    </h3>
                                </div>
                                <div class="flex items-center gap-1 ml-2">
                                    <x-tickets.table-actions :ticket="$ticket" />
                                </div>
                            </div>

                            {{-- Client and Organization --}}
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                <span class="font-medium">{{ $ticket->client->name }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $ticket->organization->name }}</span>
                            </div>

                            {{-- Department Information --}}
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                                <span>{{ $ticket->department?->departmentGroup?->name ?? 'N/A' }}</span>
                                <span class="mx-1">•</span>
                                <span>{{ $ticket->department?->name ?? 'N/A' }}</span>
                            </div>

                            {{-- Status and Priority Badges --}}
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="{{ $ticket->getStatusCssClass() }}">
                                    {{ $ticket->status_label }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                                    <x-dynamic-component :component="\App\Enums\TicketPriority::from($ticket->priority)->icon()" class="h-3 w-3 mr-1" />
                                    {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                                </span>
                            </div>

                            {{-- Assignment and Dates --}}
                            <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400">
                                <div>
                                    @if($ticket->owner)
                                        <div class="flex items-center gap-1">
                                            <div class="w-4 h-4 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                                <span class="text-white text-xs font-medium">{{ substr($ticket->owner->name, 0, 1) }}</span>
                                            </div>
                                            <span>{{ $ticket->owner->name }}</span>
                                        </div>
                                    @else
                                        <span>Unassigned</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <div>Created {{ $ticket->created_at->diffForHumans() }}</div>
                                    @if($ticket->latest_message_at)
                                        <div>Last reply {{ $ticket->latest_message_at->diffForHumans() }}</div>
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
                    @if($search || $filterStatus || $filterPriority || $filterOrg || $filterDept || $quickFilter !== 'all')
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



    {{-- Reopen Ticket Modal --}}
    @if($showReopenModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showReopenModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 wire:click="closeReopenModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-show="show" x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/40 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-arrow-path class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">
                                Reopen Ticket
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    Are you sure you want to reopen this ticket?
                                </p>
                                
                                @if(!empty($reopenTicketInfo))
                                <div class="mt-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Ticket:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">#{{ $reopenTicketInfo['ticket_number'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Subject:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ Str::limit($reopenTicketInfo['subject'] ?? 'N/A', 50) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Client:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ $reopenTicketInfo['client_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Organization:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ $reopenTicketInfo['organization_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Closed:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">
                                                @if(isset($reopenTicketInfo['closed_at']))
                                                    {{ $reopenTicketInfo['closed_at']->format('M d, Y \a\t H:i') }}
                                                    @if(($reopenTicketInfo['days_closed'] ?? 0) < 1)
                                                        ({{ $reopenTicketInfo['hours_closed'] ?? 0 }} {{ ($reopenTicketInfo['hours_closed'] ?? 0) == 1 ? 'hour' : 'hours' }} ago)
                                                    @else
                                                        ({{ $reopenTicketInfo['days_closed'] }} {{ $reopenTicketInfo['days_closed'] == 1 ? 'day' : 'days' }} ago)
                                                    @endif
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                        
                                        @if(($reopenTicketInfo['days_closed'] ?? 0) >= ($reopenTicketInfo['reopen_window_days'] ?? 3))
                                        <div class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                                            <div class="flex">
                                                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" />
                                                <div class="text-xs text-yellow-800 dark:text-yellow-200">
                                                    <p class="font-medium">Outside Standard Window</p>
                                                    <p>This ticket was closed more than {{ $reopenTicketInfo['reopen_window_days'] ?? 3 }} days ago. Consider creating a new ticket instead.</p>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Optional Reason Field --}}
                                @if(auth()->user()->hasRole(['admin', 'support']))
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Reopening Reason (Optional)
                                    </label>
                                    <textarea wire:model="reopenReason" 
                                              rows="3"
                                              placeholder="Provide a reason for reopening this ticket (optional)..."
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"></textarea>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="confirmReopenTicket" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                        Reopen Ticket
                    </button>
                    <button wire:click="closeReopenModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Close Confirmation Modal --}}
    @if($showCloseConfirmModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCloseConfirmModal') }" x-show="show" x-cloak>
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                 x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 wire:click="closeCloseConfirmModal"></div>

            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-show="show" x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-x-circle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">
                                Close Ticket
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                    To close this ticket with detailed remarks or solution summary, please use the ticket detail view.
                                </p>
                                
                                @if(!empty($closeTicketInfo))
                                <div class="mt-4 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg p-4">
                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Ticket:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">#{{ $closeTicketInfo['ticket_number'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Subject:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ Str::limit($closeTicketInfo['subject'] ?? 'N/A', 50) }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Client:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ $closeTicketInfo['client_name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="font-medium text-neutral-700 dark:text-neutral-300">Organization:</span>
                                            <span class="text-neutral-600 dark:text-neutral-400">{{ $closeTicketInfo['organization_name'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button wire:click="redirectToTicketView" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-eye class="h-4 w-4 mr-2" />
                        Go to Ticket Details
                    </button>
                    <button wire:click="quickCloseTicket" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-red-300 dark:border-red-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-x-circle class="h-4 w-4 mr-2" />
                        Quick Close
                    </button>
                    <button wire:click="closeCloseConfirmModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Merge Tickets Modal --}}
    {{-- Temporarily disabled to debug 404 issue --}}
    {{-- @livewire('tickets.merge-tickets-modal', ['ticket' => null], key('merge-modal')) --}}

    {{-- Action Confirmation Scripts --}}
    <script>
        function confirmPriorityChange(ticketId, newPriority, currentPriority, priorityLabel) {
            // Priority hierarchy for comparison
            const priorityLevels = {
                'low': 1,
                'normal': 2,
                'high': 3,
                'urgent': 4,
                'critical': 5
            };
            
            const isEscalation = priorityLevels[newPriority] > priorityLevels[currentPriority];
            
            if (isEscalation) {
                // Show confirmation for escalation
                if (confirm(`Are you sure you want to escalate this ticket's priority to ${priorityLabel}?\n\nThis action will be logged for audit purposes.`)) {
                    Livewire.dispatch('changePriority', [ticketId, newPriority]);
                }
            } else {
                // No confirmation needed for de-escalation
                Livewire.dispatch('changePriority', [ticketId, newPriority]);
            }
        }

        // Listen for Livewire component calls
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('changePriority', (data) => {
                @this.changePriority(data[0], data[1]);
            });
        });
    </script>
</div>
