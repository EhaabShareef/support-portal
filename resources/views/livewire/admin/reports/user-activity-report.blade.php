<div>
    <h1 class="text-xl font-semibold mb-4">User Activity Log</h1>

    <div class="mb-4 space-x-2">
        <input type="date" wire:model="startDate" class="rounded" />
        <input type="date" wire:model="endDate" class="rounded" />
        <input type="text" wire:model.debounce.500ms="keyword" placeholder="Search message" class="rounded" />
        <select multiple wire:model="userIds" class="rounded">
            @foreach($users as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
            @endforeach
        </select>
        <button wire:click="export" class="px-3 py-1 border rounded">Export CSV</button>
    </div>

    <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
        <thead>
            <tr>
                <th class="px-2 py-1 text-left">Time</th>
                <th class="px-2 py-1 text-left">User</th>
                <th class="px-2 py-1 text-left">Type/Action</th>
                <th class="px-2 py-1 text-left">Message</th>
                <th class="px-2 py-1 text-left">Target</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
            @foreach($activities as $a)
                <tr>
                    <td class="px-2 py-1">{{ $a->created_at }}</td>
                    <td class="px-2 py-1">{{ $a->user?->name }}</td>
                    <td class="px-2 py-1">{{ $a->activity_type }}/{{ $a->action }}</td>
                    <td class="px-2 py-1">{{ $a->message }}</td>
                    <td class="px-2 py-1">{{ class_basename($a->model_type) }}#{{ $a->model_id }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $activities->links() }}
    </div>
</div>
