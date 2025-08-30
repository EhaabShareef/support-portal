<x-settings.section title="Status Management" description="Manage ticket statuses and department group access">
    <div class="space-y-6">
        {{-- Add New Status --}}
        <div class="flex justify-between items-center">
            <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Ticket Statuses</h4>
            <button 
                wire:click="addNewStatus"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
            >
                <x-heroicon-o-plus class="h-4 w-4 inline mr-2" />
                Add New Status
            </button>
        </div>

        {{-- Status List --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($ticketStatuses as $statusKey => $status)
                <div class="p-4 border border-neutral-200 dark:border-neutral-700 rounded-lg">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background-color: {{ $status['color'] }}"></div>
                            <h5 class="font-medium text-neutral-800 dark:text-neutral-100">{{ $status['name'] }}</h5>
                            @if($status['is_protected'])
                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Protected</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <button 
                                wire:click="showDepartmentGroupAccess('{{ $statusKey }}')"
                                class="text-neutral-500 hover:text-sky-600 transition-colors duration-200"
                                title="Manage Department Access"
                            >
                                <x-heroicon-o-users class="h-4 w-4" />
                            </button>
                            @if(!$status['is_protected'])
                                <button 
                                    wire:click="deleteStatus('{{ $statusKey }}')"
                                    wire:confirm="Are you sure you want to delete this status?"
                                    class="text-neutral-500 hover:text-red-600 transition-colors duration-200"
                                    title="Delete Status"
                                >
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                </button>
                            @endif
                        </div>
                    </div>
                    
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-3">{{ $status['description'] ?: 'No description' }}</p>
                    
                    <div class="text-xs text-neutral-500 dark:text-neutral-400">
                        <div>Key: <code class="bg-neutral-100 dark:bg-neutral-800 px-1 rounded">{{ $status['key'] }}</code></div>
                        <div>Order: {{ $status['sort_order'] }}</div>
                        <div>Status: {{ $status['is_active'] ? 'Active' : 'Inactive' }}</div>
                    </div>

                    {{-- Department Groups --}}
                    @if(!empty($status['department_groups']))
                        <div class="mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-700">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Department Groups:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($departmentGroups as $group)
                                    @if(in_array($group['id'], $status['department_groups']))
                                        <span class="px-2 py-1 text-xs bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200 rounded-full">
                                            {{ $group['name'] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        @if(empty($ticketStatuses))
            <div class="text-center py-8">
                <x-heroicon-o-list-bullet class="h-12 w-12 text-neutral-400 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">No Statuses Found</h3>
                <p class="text-neutral-500 dark:text-neutral-400 mb-4">Get started by creating your first ticket status.</p>
                <button 
                    wire:click="addNewStatus"
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    Create First Status
                </button>
            </div>
        @endif
    </div>

    {{-- Add Status Modal --}}
    <x-settings.modal :show="$showAddStatusForm" title="Add New Status" size="md">
        <div class="space-y-4">
            <div>
                <label for="newStatusName" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Status Name *
                </label>
                <input 
                    type="text" 
                    wire:model="newStatusForm.name" 
                    id="newStatusName"
                    class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="e.g., In Progress"
                />
                @error('newStatusForm.name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="newStatusKey" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Status Key
                </label>
                <input 
                    type="text" 
                    wire:model="newStatusForm.key" 
                    id="newStatusKey"
                    class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="e.g., in_progress"
                />
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Leave empty to auto-generate from name</p>
                @error('newStatusForm.key') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="newStatusDescription" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Description
                </label>
                <textarea 
                    wire:model="newStatusForm.description" 
                    id="newStatusDescription"
                    rows="3"
                    class="w-full rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                    placeholder="Optional description of this status"
                ></textarea>
                @error('newStatusForm.description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="newStatusColor" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Status Color *
                </label>
                <div class="flex items-center gap-2">
                    <input 
                        type="color" 
                        wire:model="newStatusForm.color" 
                        id="newStatusColor"
                        class="h-10 w-16 rounded border-neutral-300 dark:border-neutral-600"
                    />
                    <input 
                        type="text" 
                        wire:model="newStatusForm.color" 
                        class="flex-1 rounded-md border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 focus:border-sky-500 focus:ring-sky-500"
                    />
                </div>
                @error('newStatusForm.color') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <x-slot name="footer">
            <button 
                wire:click="$set('showAddStatusForm', false)"
                class="px-4 py-2 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors duration-200"
            >
                Cancel
            </button>
            <button 
                wire:click="saveNewStatus"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
            >
                Create Status
            </button>
        </x-slot>
    </x-settings.modal>

    {{-- Department Group Access Modal --}}
    <x-settings.modal :show="$showDepartmentGroupAccess" title="Manage Department Group Access" size="lg">
        <div class="space-y-4">
            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                Select which department groups can use this status when creating or updating tickets.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($departmentGroups as $group)
                    <div class="flex items-center p-3 border border-neutral-200 dark:border-neutral-700 rounded-lg">
                        <input 
                            type="checkbox" 
                            wire:model="statusDepartmentGroups" 
                            value="{{ $group['id'] }}"
                            id="group_{{ $group['id'] }}"
                            class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded"
                        />
                        <label for="group_{{ $group['id'] }}" class="ml-3 flex-1">
                            <div class="font-medium text-neutral-800 dark:text-neutral-100">{{ $group['name'] }}</div>
                            <div class="text-sm text-neutral-500 dark:text-neutral-400">{{ $group['description'] }}</div>
                        </label>
                    </div>
                @endforeach
            </div>

            @if(empty($departmentGroups))
                <div class="text-center py-4">
                    <p class="text-sm text-neutral-500 dark:text-neutral-400">No department groups found.</p>
                </div>
            @endif
        </div>

        <x-slot name="footer">
            <button 
                wire:click="closeDepartmentGroupAccess"
                class="px-4 py-2 text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors duration-200"
            >
                Cancel
            </button>
            <button 
                wire:click="saveDepartmentGroupAccess"
                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
            >
                Save Access
            </button>
        </x-slot>
    </x-settings.modal>
</x-settings.section>
