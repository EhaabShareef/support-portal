<div class="bg-white dark:bg-neutral-800 rounded-lg border border-neutral-200 dark:border-neutral-700">
    <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">
                Related Tickets & Maintenance History
            </h3>
            <div class="flex items-center gap-2">
                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ $tickets->total() }} ticket{{ $tickets->total() !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-700/50">
        <div class="flex flex-wrap gap-4">
            <div class="min-w-48">
                <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">
                    Status
                </label>
                <select wire:model.live="statusFilter" 
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="min-w-48">
                <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide mb-1">
                    Date Range
                </label>
                <select wire:model.live="dateFilter" 
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach($dateOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Tickets List --}}
    <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
        @forelse($tickets as $ticket)
            <div class="px-6 py-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <a href="{{ route('tickets.show', $ticket) }}" 
                               class="text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 truncate">
                                {{ $ticket->subject }}
                            </a>
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">
                                #{{ $ticket->ticket_number }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-4 text-xs text-neutral-600 dark:text-neutral-400">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-building-office class="h-3 w-3" />
                                <span>{{ $ticket->organization->name }}</span>
                            </div>
                            
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-user class="h-3 w-3" />
                                <span>{{ $ticket->client->name }}</span>
                            </div>
                            
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-calendar class="h-3 w-3" />
                                <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>

                        {{-- Maintenance Note (if exists) --}}
                        @if($ticket->pivot && $ticket->pivot->maintenance_note)
                            <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border-l-2 border-yellow-400">
                                <p class="text-xs text-yellow-800 dark:text-yellow-200">
                                    <strong>Maintenance Note:</strong> {{ Str::limit($ticket->pivot->maintenance_note, 100) }}
                                </p>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center gap-2 ml-4">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" style="{{ $ticket->getStatusCssClass() }}">
                            {{ $ticket->status_label }}
                        </span>
                        
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                            {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center">
                <x-heroicon-o-ticket class="mx-auto h-8 w-8 text-neutral-400" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No related tickets</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    This hardware hasn't been linked to any tickets yet.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($tickets->hasPages())
        <div class="px-6 py-3 border-t border-neutral-200 dark:border-neutral-700">
            {{ $tickets->links() }}
        </div>
    @endif
</div>
