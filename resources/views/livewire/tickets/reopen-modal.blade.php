<div>
    @if($show)
        <form wire:submit.prevent="reopenTicket" class="space-y-2">
            <textarea wire:model="reason" class="w-full border rounded" placeholder="Reason"></textarea>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-1.5 bg-sky-600 text-white rounded">Reopen Ticket</button>
                <button type="button" wire:click="toggle" class="px-4 py-1.5 border rounded">Cancel</button>
            </div>
        </form>
    @endif
</div>
