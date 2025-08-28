<div class="flex items-center gap-1">
    {{-- View action - always available --}}
    <a href="{{ route('tickets.show', $ticket) }}" 
       class="flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200"
       aria-label="View Ticket" title="View Ticket">
        <x-heroicon-o-eye class="h-4 w-4" />
    </a>

    {{-- Show actions only when ticket is NOT closed --}}
    @if($ticket->status !== 'closed')
        @can('update', $ticket)
            <button wire:click="openCloseConfirmModal({{ $ticket->id }})" wire:loading.attr="disabled" 
                    class="flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200" 
                    aria-label="Close" title="Close Ticket">
                <x-heroicon-o-x-circle class="h-4 w-4" />
            </button>
        @endcan
        
        @can('assign', $ticket)
            @if($ticket->owner_id !== auth()->id())
                <button wire:click="assignToMe({{ $ticket->id }})" wire:loading.attr="disabled" 
                        class="flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-md transition-all duration-200" 
                        aria-label="Assign to Me" title="Assign to Me">
                    <x-heroicon-o-user-plus class="h-4 w-4" />
                </button>
            @endif
        @endcan
    @else
        {{-- When ticket is closed, only show reopen button --}}
        @can('update', $ticket)
            <button wire:click="openReopenModal({{ $ticket->id }})" wire:loading.attr="disabled" 
                    class="flex items-center p-1.5 text-neutral-600 dark:text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-all duration-200" 
                    aria-label="Reopen" title="Reopen Ticket">
                <x-heroicon-o-arrow-path class="h-4 w-4" />
            </button>
        @endcan
    @endif
</div>
