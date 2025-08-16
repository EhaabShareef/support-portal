<div>
    @if($show)
        <form wire:submit.prevent="sendMessage" class="space-y-2">
            <textarea wire:model="replyMessage" class="w-full border rounded"></textarea>
            <select wire:model="replyStatus" class="w-full border rounded">
                <option value="in_progress">In Progress</option>
                <option value="open">Open</option>
                <option value="solution_provided">Solution Provided</option>
            </select>
            <input type="file" wire:model="attachments" multiple class="w-full" />
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-1.5 bg-sky-600 text-white rounded">Send</button>
                <button type="button" wire:click="toggle" class="px-4 py-1.5 border rounded">Cancel</button>
            </div>
        </form>
    @endif
</div>
