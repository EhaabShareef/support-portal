<div class="space-y-4 p-4 border border-neutral-200 dark:border-neutral-200/20 rounded">
    <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Hardware Details</h2>
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm mb-1 text-neutral-700 dark:text-neutral-300">Hardware Type</label>
            <select wire:model="form.hardware_type_id" class="w-full px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                <option value="">-- Select Type --</option>
                @foreach($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            @error('form.hardware_type_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1 text-neutral-700 dark:text-neutral-300">Model</label>
                <input type="text" wire:model="form.model" class="w-full px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" />
                @error('form.model')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
            </div>
            <div>
                <label class="block text-sm mb-1 text-neutral-700 dark:text-neutral-300">Brand</label>
                <input type="text" wire:model="form.brand" class="w-full px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" />
                @error('form.brand')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1 text-neutral-700 dark:text-neutral-300">Quantity</label>
                <input type="number" min="1" wire:model="form.quantity" class="w-full px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" />
                @error('form.quantity')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
            </div>
            <div class="flex items-center space-x-2 pt-6">
                <input type="checkbox" wire:model="form.serial_required" id="serial_required" class="h-4 w-4" />
                <label for="serial_required" class="text-sm text-neutral-700 dark:text-neutral-300">Serials Required</label>
            </div>
        </div>
        <div>
            <label class="block text-sm mb-1 text-neutral-700 dark:text-neutral-300">Remarks</label>
            <textarea wire:model="form.remarks" class="w-full px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"></textarea>
            @error('form.remarks')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-4 py-2 bg-sky-600 text-white rounded">Save</button>
        </div>
    </form>
</div>
