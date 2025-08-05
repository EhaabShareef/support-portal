<div class="space-y-4">
    {{-- Header with Search --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Tickets</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400">View ticket history for this organization</p>
        </div>
        
        <div class="flex items-center gap-3">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                <input type="text" 
                       wire:model.live.debounce.300ms="ticketSearch"
                       placeholder="Search tickets..."
                       class="pl-10 pr-4 py-2 w-64 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
            </div>
        </div>
    </div>

    {{-- Tickets List --}}
    @if($this->filteredTickets->count() > 0)
        <div class="space-y-3">
            @foreach($this->filteredTickets as $ticket)
                <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 hover:bg-white/10 transition-all duration-200">
                    <div class="flex flex-col gap-3">
                        {{-- Ticket Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h4 class="text-base font-medium text-neutral-800 dark:text-neutral-100">
                                        {{ $ticket->subject }}
                                    </h4>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->priority === 'critical') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                        @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @else bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @endif">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        @if($ticket->status === 'open') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                        @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                        @elseif($ticket->status === 'resolved') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                        @elseif($ticket->status === 'closed') bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                        @else bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($ticket->status)) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                                        #{{ $ticket->ticket_number }}
                                    </span>
                                    @if($ticket->category)
                                    <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                        {{ $ticket->category }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                @can('tickets.show')
                                <a href="{{ route('tickets.show', $ticket) }}" 
                                   class="inline-flex items-center px-3 py-1.5 text-xs text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200">
                                    <x-heroicon-o-eye class="h-3 w-3 mr-1" />
                                    View Ticket
                                </a>
                                @endcan
                            </div>
                        </div>

                        {{-- Ticket Details Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-user class="h-3 w-3" />
                                <span class="font-medium">Client:</span>
                                <span>{{ $ticket->client ? $ticket->client->name : 'Unassigned' }}</span>
                            </div>
                            @if($ticket->assigned)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-user-circle class="h-3 w-3" />
                                <span class="font-medium">Assigned:</span>
                                <span>{{ $ticket->assigned->name }}</span>
                            </div>
                            @endif
                            @if($ticket->department)
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-building-office class="h-3 w-3" />
                                <span class="font-medium">Department:</span>
                                <span>{{ $ticket->department->name }}</span>
                            </div>
                            @endif
                            <div class="flex items-center gap-2 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-calendar class="h-3 w-3" />
                                <span class="font-medium">Created:</span>
                                <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        {{-- Ticket Description --}}
                        @if($ticket->description)
                        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            <div class="flex items-start gap-2">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="h-3 w-3 mt-0.5 text-neutral-500" />
                                <div class="flex-1">
                                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Description</span>
                                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                                        {{ Str::limit(strip_tags($ticket->description), 200) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Ticket Timeline --}}
                        <div class="flex items-center justify-between text-xs text-neutral-500 dark:text-neutral-400 border-t border-neutral-200 dark:border-neutral-700 pt-3">
                            <div class="flex items-center gap-4">
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-clock class="h-3 w-3" />
                                    Created {{ $ticket->created_at->diffForHumans() }}
                                </span>
                                @if($ticket->updated_at && $ticket->updated_at != $ticket->created_at)
                                <span class="flex items-center gap-1">
                                    <x-heroicon-o-arrow-path class="h-3 w-3" />
                                    Updated {{ $ticket->updated_at->diffForHumans() }}
                                </span>
                                @endif
                            </div>
                            @if($ticket->responses_count > 0)
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-chat-bubble-left class="h-3 w-3" />
                                <span>{{ $ticket->responses_count }} response{{ $ticket->responses_count !== 1 ? 's' : '' }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($this->filteredTickets->hasPages())
        <div class="flex justify-center mt-6">
            {{ $this->filteredTickets->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-12">
            <x-heroicon-o-ticket class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                @if($ticketSearch)
                    No tickets found matching "{{ $ticketSearch }}"
                @else
                    No tickets found
                @endif
            </h3>
            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                @if($ticketSearch)
                    Try adjusting your search criteria.
                @else
                    This organization doesn't have any tickets yet.
                @endif
            </p>
            @if($ticketSearch)
            <div class="mt-4">
                <button wire:click="$set('ticketSearch', '')" 
                        class="text-sm text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300">
                    Clear search
                </button>
            </div>
            @endif
        </div>
    @endif
</div>