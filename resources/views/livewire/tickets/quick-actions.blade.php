<div class="flex gap-2">
    <button wire:click="showReply" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Reply</button>
    <button wire:click="showNote" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Note</button>
    

<div class="flex flex-wrap gap-2">
    @can('reply', $ticket)
        <button wire:click="reply" wire:loading.attr="disabled" class="btn-secondary" aria-label="Reply">
            Reply
        </button>
    @endcan
    
    @can('addNote', $ticket)
        <button wire:click="note" wire:loading.attr="disabled" class="btn-secondary" aria-label="Add note">
            Note
        </button>
    @endcan
    
    @can('update', $ticket)
        <button wire:click="edit" wire:loading.attr="disabled" class="btn-secondary" aria-label="Edit">
            Edit
        </button>
    @endcan
    
    @can('split', $ticket)
        <button wire:click="showSplit" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Split</button>
    @endcan
    
    @if($ticket->status === 'closed')
        @can('update', $ticket)
            <button wire:click="reopen" wire:loading.attr="disabled" class="btn-secondary" aria-label="Reopen">
                Reopen
            </button>
        @endcan
    @else
        @can('update', $ticket)
            <button wire:click="close" wire:loading.attr="disabled" class="btn-danger" aria-label="Close">
                Close
            </button>
        @endcan
    @endif
    
    @can('assign', $ticket)
        <button wire:click="assignToMe" wire:loading.attr="disabled" class="btn-secondary" aria-label="Assign to Me">
            Assign to Me
        </button>
    @endcan
    
    @can('update', $ticket)
        <button wire:click="merge" wire:loading.attr="disabled" class="btn-secondary" aria-label="Merge">
            Merge
        </button>
    @endcan
</div>
