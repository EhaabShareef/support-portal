<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Ticket Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure ticket workflow, statuses, colors, and limits</p>
        </div>
        <div class="flex items-center gap-2">
            @if($hasUnsavedChanges)
                <span class="text-sm text-orange-600 dark:text-orange-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                    Unsaved changes
                </span>
            @endif
            <button wire:click="saveSettings" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-check class="h-4 w-4 mr-2" />
                Save Changes
            </button>
            <button wire:click="resetToDefaults" 
                wire:confirm="Are you sure you want to reset all ticket settings to their defaults? This cannot be undone."
                class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                Reset All
            </button>
        </div>
    </div>

    {{-- Workflow Settings --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Workflow Settings</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure how tickets behave throughout their lifecycle</p>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Default Reply Status --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Default Status on Reply
                    <span class="text-xs text-neutral-500 block mt-1">Status assigned after a reply if user doesn't choose one</span>
                </label>
                <select wire:model.live="defaultReplyStatus" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @foreach($this->statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('defaultReplyStatus') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Reopen Window Days --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Reopen Window (Days)
                    <span class="text-xs text-neutral-500 block mt-1">Clients may reopen closed tickets within this many days</span>
                </label>
                <input type="number" wire:model.live="reopenWindowDays" min="1" max="365"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                @error('reopenWindowDays') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Message Order --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Message Ordering
                    <span class="text-xs text-neutral-500 block mt-1">Order of messages in ticket view</span>
                </label>
                <select wire:model.live="messageOrder" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @foreach($this->messageOrderOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('messageOrder') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Priority Escalation Confirmation --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Priority Escalation Policy</label>
                <div class="flex items-center justify-between p-3 bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg">
                    <div>
                        <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Require Confirmation</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">Support/Admin must confirm when raising priority</div>
                    </div>
                    <button type="button" wire:click="$toggle('requireEscalationConfirmation')" 
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $requireEscalationConfirmation ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                        <span class="sr-only">Require escalation confirmation</span>
                        <span aria-hidden="true" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireEscalationConfirmation ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Attachment Settings --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Attachment Settings</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure file upload limits for ticket attachments</p>
        </div>
        
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Max Size --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Max File Size (MB)
                    <span class="text-xs text-neutral-500 block mt-1">Maximum size per attachment file</span>
                </label>
                <input type="number" wire:model.live="attachmentMaxSizeMb" min="1" max="100"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                @error('attachmentMaxSizeMb') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Max Count --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Max File Count
                    <span class="text-xs text-neutral-500 block mt-1">Maximum files per reply</span>
                </label>
                <input type="number" wire:model.live="attachmentMaxCount" min="1" max="20"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                @error('attachmentMaxCount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>

    {{-- Color Settings --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Status & Priority Colors</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Customize badge colors for ticket statuses and priorities</p>
                </div>
                <button wire:click="resetColors" 
                    wire:confirm="Are you sure you want to reset all colors to their defaults?"
                    class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset Colors
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Status Colors --}}
                <div>
                    <h4 class="text-md font-medium text-neutral-800 dark:text-neutral-100 mb-4">Status Colors</h4>
                    <div class="space-y-3">
                        @foreach($statusColors as $status => $color)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $color }}"></div>
                                    <div>
                                        <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100 capitalize">{{ str_replace('_', ' ', $status) }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $color }}</div>
                                    </div>
                                </div>
                                <button wire:click="editColor('status', '{{ $status }}', '{{ $color }}')" 
                                    class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                    title="Edit color">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Priority Colors --}}
                <div>
                    <h4 class="text-md font-medium text-neutral-800 dark:text-neutral-100 mb-4">Priority Colors</h4>
                    <div class="space-y-3">
                        @foreach($priorityColors as $priority => $color)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $color }}"></div>
                                    <div>
                                        <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100 capitalize">{{ $priority }}</div>
                                        <div class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $color }}</div>
                                    </div>
                                </div>
                                <button wire:click="editColor('priority', '{{ $priority }}', '{{ $color }}')" 
                                    class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                    title="Edit color">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ticket Status Management --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Ticket Status Management</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure available ticket statuses and department group assignments</p>
                </div>
                <button wire:click="createStatus" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Status
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if($this->ticketStatuses->count() > 0)
                <div class="space-y-4">
                    @foreach($this->ticketStatuses as $status)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg">
                            {{-- Status Header --}}
                            <div class="px-4 py-3 border-b border-neutral-200 dark:border-neutral-600">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $status->color }}"></div>
                                        <div>
                                            <h4 class="font-medium text-neutral-800 dark:text-neutral-100 flex items-center gap-2">
                                                {{ $status->name }}
                                                @if($status->is_protected)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">
                                                        <x-heroicon-o-shield-check class="h-3 w-3 mr-1" />
                                                        Protected
                                                    </span>
                                                @endif
                                                @if(!$status->is_active)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </h4>
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $status->key }}</p>
                                            @if($status->description)
                                                <p class="text-xs text-neutral-500 mt-1">{{ $status->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if(!$status->is_protected)
                                            <button wire:click="editStatus({{ $status->id }})" 
                                                class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                                title="Edit">
                                                <x-heroicon-o-pencil-square class="h-4 w-4" />
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Department Group Assignments --}}
                            <div class="p-4">
                                <h5 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">Department Group Assignments</h5>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach($this->departmentGroups as $departmentGroup)
                                        @php
                                            $isAssigned = in_array($departmentGroup->id, $this->statusDepartmentGroups[$status->id] ?? []);
                                        @endphp
                                        <label class="flex items-center space-x-2 p-2 rounded-lg border {{ $isAssigned ? 'bg-sky-50 border-sky-200 dark:bg-sky-900/20 dark:border-sky-800' : 'bg-white border-neutral-200 dark:bg-neutral-900/50 dark:border-neutral-600' }} cursor-pointer hover:shadow-sm transition-all">
                                            <input type="checkbox" 
                                                   wire:change="updateDepartmentGroupAssignment({{ $status->id }}, {{ $departmentGroup->id }}, $event.target.checked)"
                                                   {{ $isAssigned ? 'checked' : '' }}
                                                   class="rounded border-neutral-300 text-sky-600 focus:ring-sky-500">
                                            <span class="text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $departmentGroup->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-ticket class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No ticket statuses</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first ticket status.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Color Edit Modal --}}
@if($showColorModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeColorModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <form wire:submit="saveColor">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            Edit {{ ucfirst($editingColorType) }} Color
                        </h3>
                        <button type="button" wire:click="closeColorModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Color for "{{ ucfirst(str_replace('_', ' ', $editingColorKey)) }}"
                            </label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="editingColorValue" 
                                       class="w-16 h-10 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                <input type="text" wire:model.live="editingColorValue" 
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                       placeholder="#3b82f6">
                            </div>
                            @error('editingColorValue') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Color Preview --}}
                        <div class="p-4 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg">
                            <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Preview:</div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white" 
                                  style="background-color: {{ $editingColorValue }}">
                                {{ ucfirst(str_replace('_', ' ', $editingColorKey)) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
                        Save Color
                    </button>
                    <button type="button" wire:click="closeColorModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Status Modal --}}
@if($showStatusModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeStatusModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveStatus">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $statusEditMode ? 'Edit Ticket Status' : 'Add New Ticket Status' }}
                        </h3>
                        <button type="button" wire:click="closeStatusModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model="statusForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('statusForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Key *</label>
                            <input type="text" wire:model="statusForm.key" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono text-sm"
                                   placeholder="open">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Internal identifier (lowercase, underscores only)</p>
                            @error('statusForm.key') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description</label>
                            <textarea wire:model="statusForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                            @error('statusForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Color *</label>
                            <div class="flex items-center gap-3">
                                <input type="color" wire:model.live="statusForm.color" 
                                       class="w-16 h-10 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                <input type="text" wire:model.live="statusForm.color" 
                                       class="flex-1 px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                       placeholder="#3b82f6">
                            </div>
                            @error('statusForm.color') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="statusForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('statusForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Make this status available for use</span>
                            </span>
                            <button type="button" wire:click="$toggle('statusForm.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $statusForm['is_active'] ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Active</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $statusForm['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $statusEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeStatusModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Unsaved Changes Warning --}}
@if($hasUnsavedChanges)
<script>
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
});
</script>
@endif