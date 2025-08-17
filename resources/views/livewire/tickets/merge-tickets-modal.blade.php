<div>
    @if($show)
        <div class="p-4 border bg-white">
            <h2 class="mb-2">Merge Tickets</h2>
            <input type="text" wire:model="ticketsInput" placeholder="Ticket IDs comma separated" class="border p-1 w-full" />
            <div class="mt-4 flex justify-end space-x-2">
                <button wire:click="toggle" class="px-4 py-2 bg-gray-200">Cancel</button>
                <button wire:click="merge" class="px-4 py-2 bg-gray-200">Merge</button>
            </div>
        </div>
    @endif
</div>
