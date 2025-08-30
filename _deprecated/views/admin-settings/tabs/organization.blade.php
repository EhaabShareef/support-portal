<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Organization Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage organization subscription statuses</p>
        </div>
    </div>

    {{-- Subscription Status Management --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Subscription Statuses</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure available organization subscription status options</p>
                </div>
                <button wire:click="createStatus" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Status
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if($this->subscriptionStatuses->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->subscriptionStatuses as $status)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-4 h-4 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $status->color }}"></div>
                                        <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm">{{ $status->label }}</h4>
                                        @if(!$status->is_active)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-neutral-600 dark:text-neutral-400 mb-1 font-mono">{{ $status->key }}</p>
                                    @if($status->description)
                                        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">{{ $status->description }}</p>
                                    @endif
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono">{{ $status->color }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button wire:click="editStatus({{ $status->id }})" 
                                        class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                        title="Edit">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleStatusActive({{ $status->id }})" 
                                        class="text-neutral-500 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors p-1" 
                                        title="{{ $status->is_active ? 'Disable' : 'Enable' }}">
                                        @if($status->is_active)
                                            <x-heroicon-o-eye-slash class="h-4 w-4" />
                                        @else
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                        @endif
                                    </button>
                                    @if($confirmingStatusDelete === $status->id)
                                        <button wire:click="deleteStatus" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors p-1" 
                                            title="Confirm delete">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancelStatusDelete" 
                                            class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors p-1" 
                                            title="Cancel">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button wire:click="confirmDeleteStatus({{ $status->id }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort Order: {{ $status->sort_order }}</span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">{{ $status->organizations_count ?? 0 }} orgs</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-building-office class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No subscription statuses</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Create your first organization subscription status to categorize organizations.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Integration Info --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium mb-1">Subscription Status Integration</p>
                <p>Subscription statuses are used throughout the application to categorize organizations based on their billing and access status. These statuses control organization visibility and access permissions.</p>
            </div>
        </div>
    </div>
</div>

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
                            {{ $statusEditMode ? 'Edit Subscription Status' : 'Add New Subscription Status' }}
                        </h3>
                        <button type="button" wire:click="closeStatusModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Label *</label>
                            <input type="text" wire:model.live="statusForm.label" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('statusForm.label') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Key *</label>
                            <input type="text" wire:model="statusForm.key" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono text-sm"
                                   placeholder="active">
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

                        {{-- Color Preview --}}
                        <div class="p-4 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg">
                            <div class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Preview:</div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded-full border-2 border-neutral-200 dark:border-neutral-600" style="background-color: {{ $statusForm['color'] }}"></div>
                                <span class="text-sm text-neutral-800 dark:text-neutral-100">{{ $statusForm['label'] ?: 'Subscription Status' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
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