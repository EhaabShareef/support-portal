<div class="space-y-4 p-4 border border-neutral-200 dark:border-neutral-200/20 rounded">
    <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Select Hardware Contract</h2>
    <div class="flex space-x-2 items-end">
        <select wire:model="selected" class="flex-1 px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
            <option value="">-- Choose Contract --</option>
            @foreach($contracts as $contract)
                <option value="{{ $contract->id }}">{{ $contract->title ?? ('Contract #' . $contract->id) }}</option>
            @endforeach
        </select>
        <button wire:click="selectContract" class="px-4 py-2 bg-sky-600 text-white rounded">Next</button>
    </div>
</div>
