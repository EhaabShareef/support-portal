<div class="flex flex-wrap items-center gap-2">
    {{-- Back to Tickets button (only show when not in manage-tickets view) --}}
    @if(!request()->routeIs('tickets.index'))
        <a href="{{ route('tickets.index') }}" 
           class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-300 ease-in-out overflow-hidden"
           aria-label="Back to Tickets" title="Back to Tickets">
            <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                Back to Tickets
            </span>
            <x-heroicon-o-arrow-left class="h-5 w-5 transition-transform duration-300 group-hover:-translate-x-0.5 flex-shrink-0" />
        </a>
    @endif

    {{-- Show all actions only when ticket is NOT closed --}}
    @if($ticket->status !== 'closed')
        @can('reply', $ticket)
            <button wire:click="reply" wire:loading.attr="disabled" 
                    class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                    aria-label="Reply" title="Reply">
                <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                    Reply
                </span>
                <x-heroicon-o-chat-bubble-left class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
            </button>
        @endcan
        
        @can('addNote', $ticket)
            <button wire:click="note" wire:loading.attr="disabled" 
                    class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                    aria-label="Add note" title="Add Internal Note">
                <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                    Add Note
                </span>
                <x-heroicon-o-document-text class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
            </button>
        @endcan
        
        {{-- Only show edit button when not in manage-tickets view --}}
        @if(!request()->routeIs('tickets.index'))
            @can('update', $ticket)
                <button wire:click="edit" wire:loading.attr="disabled" 
                        class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                        aria-label="Edit" title="Edit Ticket">
                    <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                        Edit
                    </span>
                    <x-heroicon-o-pencil class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
                </button>
            @endcan
        @endif
        @can('update', $ticket)
            <button wire:click="close" wire:loading.attr="disabled" 
                    class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                    aria-label="Close" title="Close Ticket">
                <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                    Close
                </span>
                <x-heroicon-o-x-circle class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
            </button>
        @endcan
        
        @can('assign', $ticket)
            @if($ticket->owner_id !== auth()->id())
                <button wire:click="assignToMe" wire:loading.attr="disabled" 
                        class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                        aria-label="Assign to Me" title="Assign to Me">
                    <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                        Assign to Me
                    </span>
                    <x-heroicon-o-user-plus class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
                </button>
            @endif
        @endcan
        
        @can('split', $ticket)
            <button wire:click="showSplit" wire:loading.attr="disabled" 
                    class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                    aria-label="Split Ticket" title="Split Ticket">
                <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                    Split
                </span>
                <x-heroicon-o-scissors class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
            </button>
        @endcan
        

    @else
        {{-- When ticket is closed, only show reopen button --}}
        @can('update', $ticket)
            <button wire:click="reopen" wire:loading.attr="disabled" 
                    class="group relative flex items-center p-2.5 text-neutral-600 dark:text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/30 rounded-md transition-all duration-300 ease-in-out overflow-hidden" 
                    aria-label="Reopen" title="Reopen Ticket">
                <span class="text-sm font-medium w-0 opacity-0 group-hover:w-auto group-hover:opacity-100 group-hover:mr-2 transition-all duration-300 ease-in-out whitespace-nowrap overflow-hidden">
                    Reopen
                </span>
                <x-heroicon-o-arrow-path class="h-5 w-5 flex-shrink-0 transition-transform duration-300 group-hover:scale-110" />
            </button>
        @endcan
    @endif
</div>
