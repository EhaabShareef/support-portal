<div>
    @if($show)
        <form wire:submit.prevent="closeTicket" class="space-y-2">
            <textarea wire:model="remarks" class="w-full border rounded" placeholder="Remarks"></textarea>
            <textarea wire:model="solution" class="w-full border rounded" placeholder="Solution"></textarea>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-1.5 bg-red-600 text-white rounded">Close Ticket</button>
                <button type="button" wire:click="toggle" class="px-4 py-1.5 border rounded">Cancel</button>
            </div>
        </form>
    @endif
</div>
