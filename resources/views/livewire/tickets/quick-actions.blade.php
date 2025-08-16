<div class="flex gap-2">
    <button wire:click="showReply" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Reply</button>
    <button wire:click="showNote" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Note</button>
    @if($ticket->status === 'closed')
        <button wire:click="showReopen" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Reopen</button>
    @else
        <button wire:click="showClose" class="inline-flex items-center px-3 py-1.5 text-xs border border-neutral-300 rounded-md">Close</button>
    @endif
</div>
