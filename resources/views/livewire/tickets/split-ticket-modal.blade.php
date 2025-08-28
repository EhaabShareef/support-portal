<div>
    @if($show)
        <div class="p-4 border bg-white">
            <h2 class="mb-2">Split Ticket</h2>
            <div class="mb-4">
                <label class="block mb-1 text-sm">From which message to split</label>
                <select wire:model="startMessageId" class="border p-1 w-full">
                    <option value="">Select message</option>
                    @foreach($messagesList as $m)
                        <option value="{{ $m['id'] }}">{{ $m['preview'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="closeOriginal" class="mr-2">
                    <span>Close original ticket after split.</span>
                </label>
            </div>
            <div class="mb-4">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model="copyNotes" class="mr-2">
                    <span>Copy notes from original ticket.</span>
                </label>
            </div>
            <div class="mt-4 flex justify-end space-x-2">
                <button wire:click="toggle" class="px-4 py-2 bg-gray-200">Cancel</button>
                <button wire:click="split" class="px-4 py-2 bg-gray-200">Split</button>
            </div>
        </div>
    @endif
</div>
