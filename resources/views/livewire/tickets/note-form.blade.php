<div>
    @if($show)
        <form wire:submit.prevent="addNote" class="space-y-2">
            <textarea wire:model="note" class="w-full border rounded"></textarea>
            <select wire:model="noteColor" class="w-full border rounded">
                <option value="sky">Sky</option>
                <option value="yellow">Yellow</option>
                <option value="red">Red</option>
            </select>
            <label class="flex items-center space-x-2">
                <input type="checkbox" wire:model="noteInternal" />
                <span>Internal</span>
            </label>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-1.5 bg-sky-600 text-white rounded">Save</button>
                <button type="button" wire:click="toggle" class="px-4 py-1.5 border rounded">Cancel</button>
            </div>
        </form>
    @endif
</div>
