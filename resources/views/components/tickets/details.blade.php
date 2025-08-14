@props(['ticket', 'editMode', 'form', 'departments', 'users', 'statusOptions', 'priorityOptions'])

<div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Ticket Details</h3>
    </div>
    
    <div class="px-6 py-4">
        @if($editMode)
            {{-- Edit Form --}}
            <form wire:submit="updateTicket" class="space-y-4" onsubmit="return confirmTicketUpdate(event, '{{ $ticket->priority }}')">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Priority</label>
                        <select wire:model="form.priority" id="prioritySelect"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            @foreach($priorityOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Status</label>
                        <select wire:model="form.status"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Owner</label>
                        <select wire:model="form.owner_id"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department</label>
                        <select wire:model="form.department_id"
                                class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white/60 dark:bg-neutral-900/50 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" wire:click="cancelEdit"
                            class="inline-flex items-center px-4 py-2 bg-neutral-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-neutral-700">
                        Cancel
                    </button>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700">
                        Save Changes
                    </button>
                </div>
            </form>
        @else
            {{-- Display Mode --}}
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Priority</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketPriority::from($ticket->priority)->cssClass() }}">
                                    {{ \App\Enums\TicketPriority::from($ticket->priority)->label() }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Status</label>
                            <div class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ \App\Enums\TicketStatus::from($ticket->status)->cssClass() }}">
                                    {{ \App\Enums\TicketStatus::from($ticket->status)->label() }}
                                </span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Department</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                <div>{{ $ticket->department->departmentGroup->name ?? 'N/A' }}</div>
                                <div class="text-neutral-600 dark:text-neutral-400">{{ $ticket->department->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Client</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                <div>{{ $ticket->client->name ?? 'N/A' }}</div>
                                <div class="text-neutral-600 dark:text-neutral-400">{{ $ticket->organization->name ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Owner</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                {{ $ticket->owner->name ?? 'Unassigned' }}
                            </div>
                        </div>

                        {{-- Active Contract --}}
                        @if($this->activeContract)
                            <div>
                                <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Active Contract</label>
                                <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                    <div>{{ $this->activeContract->contract_number }}</div>
                                    <div class="text-neutral-600 dark:text-neutral-400">
                                        {{ $this->activeContract->type }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>