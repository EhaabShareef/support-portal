@props(['ticket'])

<div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
    <div class="px-6 py-4">
        {{-- Main header with subject, ticket number, and badges --}}
        <div class="flex justify-between items-start mb-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-neutral-800 dark:text-neutral-200 truncate">
                        {{ $ticket->subject }}
                    </h1>
                    <x-status-badge :status="$ticket->status" />
                    <x-priority-badge :priority="$ticket->priority" />
                </div>
                <div class="flex items-center gap-4 text-sm text-neutral-500 dark:text-neutral-400">
                    <span>Created {{ $ticket->created_at->format('M d, Y \a\t H:i') }}</span>
                    @if($ticket->updated_at->ne($ticket->created_at))
                        <span>•</span>
                        <span>Updated {{ $ticket->updated_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <div class="text-sm font-medium text-neutral-600 dark:text-neutral-400">
                    Ticket #{{ $ticket->ticket_number }}
                </div>
            </div>
        </div>

        {{-- Quick actions row --}}
        <div class="pt-4 border-t border-neutral-200/50 dark:border-neutral-700/50">
            @livewire('tickets.quick-actions', ['ticket' => $ticket])
        </div>
    </div>
</div>