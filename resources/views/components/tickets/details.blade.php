@props(['ticket', 'editMode', 'form', 'departments', 'users', 'statusOptions', 'priorityOptions'])

<div class="bg-white/60 dark:bg-neutral-900/50 backdrop-blur-sm rounded-lg border border-neutral-200/50 dark:border-neutral-700/50 mb-6">
    <div class="px-6 py-4 border-b border-neutral-200/50 dark:border-neutral-700/50">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-200">Ticket Details</h3>
    </div>
    
    <div class="px-6 py-4">
        @if($editMode)
            {{-- Edit Form --}}
            <form class="space-y-4" onsubmit="return confirmTicketUpdate(event, '{{ $ticket->priority }}')">
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
                    <button type="button" wire:click="updateTicket" onclick="return handleSaveClick('{{ $ticket->priority }}')"
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
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->getStatusCssClass() }}">
                                    {{ $ticket->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Organization</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                <span class="truncate" title="{{ $ticket->organization->name ?? 'N/A' }}">{{ $ticket->organization->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Client</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                <span class="truncate" title="{{ $ticket->client->name ?? 'N/A' }}">{{ $ticket->client->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Client Contact</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200 space-y-1">
                                @if($ticket->client->email)
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-envelope class="h-3 w-3 text-neutral-400" />
                                        <a href="mailto:{{ $ticket->client->email }}" class="text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 truncate" title="{{ $ticket->client->email }}">
                                            {{ $ticket->client->email }}
                                        </a>
                                    </div>
                                @endif
                                @if($ticket->client->phone)
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-phone class="h-3 w-3 text-neutral-400" />
                                        <a href="tel:{{ $ticket->client->phone }}" class="text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 truncate" title="{{ $ticket->client->phone }}">
                                            {{ $ticket->client->phone }}
                                        </a>
                                    </div>
                                @endif
                                @if(!$ticket->client->email && !$ticket->client->phone)
                                    <span class="text-neutral-500 dark:text-neutral-400">No contact information</span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Owner</label>
                            <div class="mt-1 text-sm text-neutral-800 dark:text-neutral-200">
                                <span class="truncate" title="{{ $ticket->owner->name ?? 'Unassigned' }}">{{ $ticket->owner->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Contract Information - Full Width with Border --}}
                <div class="pt-4 mt-4 border-t border-neutral-200 dark:border-neutral-700">
                    @php
                        $departmentContracts = $ticket->organization->contracts->where('department_id', $ticket->department_id);
                        $hasActiveContracts = $departmentContracts->where('status', 'active')->count() > 0;
                    @endphp
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Contract Status</label>
                            <div class="mt-1">
                                @if($hasActiveContracts)
                                    @foreach($departmentContracts->where('status', 'active') as $contract)
                                        <div class="text-sm text-neutral-800 dark:text-neutral-200 mb-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-neutral-600 dark:text-neutral-400">{{ $contract->type }}</span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-200">
                                                    Active
                                                </span>
                                            </div>
                                            @if($contract->csi_number)
                                                <div class="text-sky-600 dark:text-sky-400 text-xs font-medium">
                                                    CSI: {{ $contract->csi_number }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-sm text-neutral-800 dark:text-neutral-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-neutral-600 dark:text-neutral-400">No active contract</span>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200">
                                                Warning
                                            </span>
                                        </div>
                                        <div class="text-neutral-500 dark:text-neutral-500 text-xs">
                                            {{ $ticket->department->name }} department
                                        </div>
                                    </div>
                                @endif
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
                </div>
            </div>
        @endif
    </div>
</div>