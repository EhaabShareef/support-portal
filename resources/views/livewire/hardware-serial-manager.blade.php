<div class="space-y-4 p-4 border border-neutral-200 dark:border-neutral-200/20 rounded">
    <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Serial Numbers</h2>
    <div class="flex space-x-2">
        <input type="text" wire:model="serialInput" wire:keydown.enter.prevent="addSerial" class="flex-1 px-3 py-2 border rounded bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100" placeholder="Enter serial" />
        <button wire:click="addSerial" class="px-4 py-2 bg-sky-600 text-white rounded">Add</button>
    </div>
    <div class="text-sm text-neutral-700 dark:text-neutral-300">{{ $progress }} / {{ $targetCount }} serials captured</div>
    <ul class="space-y-1 max-h-64 overflow-y-auto custom-scrollbar scrollbar-on-hover">
        @foreach($serials as $s)
            <li class="flex justify-between items-center border-b border-neutral-200 dark:border-neutral-700 py-1">
                <span>{{ $s->serial }}</span>
                <button wire:click="removeSerial({{ $s->id }})" class="text-red-600">&times;</button>
            </li>
        @endforeach
    </ul>
</div>
