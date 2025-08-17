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

    {{-- Priority Colors --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Priority Colors</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Customize badge colors for ticket priorities</p>
                </div>
                <button wire:click="resetPriorityColors" 
                    wire:confirm="Are you sure you want to reset priority colors to defaults?"
                    class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                    Reset Colors
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($priorityColors as $priority => $color)
                    @if($editingPriorityKey === $priority)
                        {{-- Edit Form --}}
                        <div class="bg-white dark:bg-neutral-900/50 border-2 border-orange-300 dark:border-orange-600 rounded-lg p-4">
                            <form wire:submit="savePriorityColor">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                            {{ ucfirst($priority) }} Priority Color
                                        </label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" wire:model.live="editPriorityForm.color" 
                                                   class="w-12 h-8 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                            <input type="text" wire:model.live="editPriorityForm.color" 
                                                   class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                                   placeholder="#3b82f6">
                                        </div>
                                        @error('editPriorityForm.color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex gap-2 pt-2">
                                        <button type="submit" 
                                                class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                            Save
                                        </button>
                                        <button type="button" wire:click="cancelEditPriorityColor"
                                                class="flex-1 bg-neutral-500 hover:bg-neutral-600 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- Display Card --}}
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-6 h-6 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $color }}"></div>
                                    <div>
                                        <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm capitalize">{{ $priority }}</h4>
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $color }}</p>
                                    </div>
                                </div>
                                <button wire:click="editPriorityColor('{{ $priority }}')" 
                                    class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                    title="Edit color">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- Ticket Status Management --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Status Management</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure available ticket statuses with custom colors</p>
                </div>
                <button wire:click="addNewStatus" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Status
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Add New Status Card --}}
                @if($showAddStatusForm)
                    <div class="bg-white dark:bg-neutral-900/50 border-2 border-dashed border-sky-300 dark:border-sky-600 rounded-lg p-4">
                        <form wire:submit="saveNewStatus">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                    <input type="text" wire:model.live.debounce.300ms="newStatusForm.name" 
                                           class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                           placeholder="e.g., In Review" required>
                                    @error('newStatusForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Key *</label>
                                    <input type="text" wire:model="newStatusForm.key" 
                                           class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                           placeholder="in_review" required>
                                    @error('newStatusForm.key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                    <textarea wire:model="newStatusForm.description" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                              placeholder="Brief description"></textarea>
                                    @error('newStatusForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color *</label>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="newStatusForm.color" 
                                               class="w-10 h-8 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                        <input type="text" wire:model.live="newStatusForm.color" 
                                               class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                               placeholder="#3b82f6">
                                    </div>
                                    @error('newStatusForm.color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Groups</label>
                                    <p class="text-xs text-neutral-500 mb-2">Select which department groups can use this status (leave empty for all)</p>
                                    <div class="space-y-1 max-h-32 overflow-y-auto">
                                        @foreach($this->departmentGroups as $group)
                                            <label class="flex items-center" wire:key="new-department-group-{{ $group->id }}">
                                                <input type="checkbox" 
                                                       wire:model.live="newStatusForm.department_groups" 
                                                       value="{{ $group->id }}"
                                                       class="rounded border-neutral-300 dark:border-neutral-600 text-sky-600 focus:ring-sky-500 focus:ring-offset-0 dark:bg-neutral-900">
                                                <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">{{ $group->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-2">
                                    <button type="submit" 
                                            class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                        Save
                                    </button>
                                    <button type="button" wire:click="cancelAddStatus"
                                            class="flex-1 bg-neutral-500 hover:bg-neutral-600 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Existing Statuses --}}
                @foreach($ticketStatusesArray as $key => $status)
                    @if($editingStatusKey === $key)
                        {{-- Edit Form --}}
                        <div class="bg-white dark:bg-neutral-900/50 border-2 border-orange-300 dark:border-orange-600 rounded-lg p-4">
                            <form wire:submit="saveEditStatus">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                        <input type="text" wire:model="editStatusForm.name" 
                                               class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               required>
                                        @error('editStatusForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Key *</label>
                                        <input type="text" wire:model="editStatusForm.key" 
                                               class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono"
                                               required>
                                        @error('editStatusForm.key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                                        <textarea wire:model="editStatusForm.description" rows="2"
                                                  class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                        @error('editStatusForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Color *</label>
                                        <div class="flex items-center gap-2">
                                            <input type="color" wire:model.live="editStatusForm.color" 
                                                   class="w-10 h-8 border border-neutral-300 dark:border-neutral-600 rounded cursor-pointer">
                                            <input type="text" wire:model.live="editStatusForm.color" 
                                                   class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono">
                                        </div>
                                        @error('editStatusForm.color') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Department Groups</label>
                                        <p class="text-xs text-neutral-500 mb-2">Select which department groups can use this status (leave empty for all)</p>
                                        <div class="space-y-1 max-h-32 overflow-y-auto">
                                            @foreach($this->departmentGroups as $group)
                                                <label class="flex items-center" wire:key="edit-department-group-{{ $group->id }}">
                                                    <input type="checkbox" 
                                                           wire:model.live="editStatusForm.department_groups" 
                                                           value="{{ $group->id }}"
                                                           class="rounded border-neutral-300 dark:border-neutral-600 text-sky-600 focus:ring-sky-500 focus:ring-offset-0 dark:bg-neutral-900">
                                                    <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">{{ $group->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="flex gap-2 pt-2">
                                        <button type="submit" 
                                                class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                            Update
                                        </button>
                                        <button type="button" wire:click="cancelEditStatus"
                                                class="flex-1 bg-neutral-500 hover:bg-neutral-600 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        {{-- Display Card --}}
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm flex items-center gap-2">
                                        <div class="w-4 h-4 rounded-full" style="background-color: {{ $status['color'] }}"></div>
                                        {{ $status['name'] }}
                                        @if($status['is_protected'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200">
                                                Protected
                                            </span>
                                        @endif
                                        @if(!$status['is_active'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </h4>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 font-mono mt-1">{{ $status['key'] }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    @if(!$status['is_protected'])
                                        <button wire:click="startEditStatus('{{ $key }}')" 
                                            class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                            title="Edit">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </button>
                                    @endif
                                    <button wire:click="toggleStatusActive('{{ $key }}')" 
                                        class="text-neutral-500 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors p-1" 
                                        title="{{ $status['is_active'] ? 'Disable' : 'Enable' }}">
                                        @if($status['is_active'])
                                            <x-heroicon-o-eye-slash class="h-4 w-4" />
                                        @else
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                        @endif
                                    </button>
                                    @if(!$status['is_protected'])
                                        <button wire:click="deleteStatus('{{ $key }}')" 
                                            wire:confirm="Are you sure you want to delete this status?"
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if($status['description'])
                                <p class="text-xs text-neutral-600 dark:text-neutral-400">{{ $status['description'] }}</p>
                            @endif
                            
                            @if(!empty($status['department_groups']))
                                <div class="mt-2">
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">Department Groups:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $groups = $this->departmentGroups->keyBy('id');
                                        @endphp
                                        @foreach($status['department_groups'] as $groupId)
                                            @php
                                                $group = $groups->get($groupId);
                                            @endphp
                                            @if($group)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">
                                                    {{ $group->name }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                        All Department Groups
                                    </span>
                                </div>
                            @endif
                            
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort: {{ $status['sort_order'] }}</span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $status['color'] }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                {{-- Empty State --}}
                @if(count($ticketStatusesArray) == 0 && !$showAddStatusForm)
                    <div class="col-span-full text-center py-12">
                        <x-heroicon-o-ticket class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No ticket statuses</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add your first ticket status.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


{{-- Unsaved Changes Warning --}}
@if($hasUnsavedChanges)
<script>
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
});
</script>
@endif