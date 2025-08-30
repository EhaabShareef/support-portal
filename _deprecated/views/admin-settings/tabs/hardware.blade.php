<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">Hardware Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage hardware types and statuses for organization equipment</p>
        </div>
    </div>

    {{-- Hardware Types Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Hardware Types</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Define the types of hardware equipment your organization manages</p>
                </div>
                <button wire:click="createType" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Type
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($hardwareTypes as $type)
                    <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm flex items-center gap-2">
                                    <x-heroicon-o-computer-desktop class="h-4 w-4 text-neutral-500" />
                                    {{ $type['name'] }}
                                    @if($type['is_protected'])
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200" title="Protected - cannot be deleted">
                                            <x-heroicon-o-shield-check class="h-3 w-3 mr-1" />
                                            Protected
                                        </span>
                                    @endif
                                </h4>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono mt-1">{{ $type['slug'] }}</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button wire:click="editType({{ $type['id'] }})" 
                                    class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                    title="Edit">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                                @if(!$type['is_protected'])
                                    @if($confirmingTypeDelete === $type['id'])
                                        <button wire:click="deleteType" 
                                            class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 transition-colors p-1" 
                                            title="Confirm delete">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancelTypeDelete" 
                                            class="text-neutral-500 hover:text-neutral-700 dark:hover:text-neutral-300 transition-colors p-1" 
                                            title="Cancel">
                                            <x-heroicon-o-x-mark class="h-4 w-4" />
                                        </button>
                                    @else
                                        <button wire:click="confirmDeleteType({{ $type['id'] }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                @else
                                    <span class="text-neutral-300 p-1" title="Protected items cannot be deleted">
                                        <x-heroicon-o-shield-check class="h-4 w-4" />
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-600">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort Order: {{ $type['sort_order'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Hardware Statuses Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Hardware Statuses</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Define the possible statuses for hardware lifecycle management</p>
                </div>
                <button wire:click="createStatus" 
                    class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Status
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($hardwareStatuses as $status)
                    <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm flex items-center gap-2">
                                    @php
                                        $statusIcon = match($status['slug']) {
                                            'active' => 'heroicon-o-check-circle',
                                            'inactive' => 'heroicon-o-x-circle',
                                            'maintenance' => 'heroicon-o-wrench-screwdriver',
                                            'retired' => 'heroicon-o-archive-box',
                                            'under_repair' => 'heroicon-o-cog-6-tooth',
                                            default => 'heroicon-o-circle-stack'
                                        };
                                        $iconColor = match($status['slug']) {
                                            'active' => 'text-green-500',
                                            'inactive' => 'text-red-500',
                                            'maintenance' => 'text-yellow-500',
                                            'retired' => 'text-neutral-500',
                                            'under_repair' => 'text-blue-500',
                                            default => 'text-neutral-500'
                                        };
                                    @endphp
                                    <x-dynamic-component :component="$statusIcon" class="h-4 w-4 {{ $iconColor }}" />
                                    {{ $status['name'] }}
                                    @if($status['is_protected'])
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200" title="Protected - cannot be deleted">
                                            <x-heroicon-o-shield-check class="h-3 w-3 mr-1" />
                                            Protected
                                        </span>
                                    @endif
                                </h4>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 font-mono mt-1">{{ $status['slug'] }}</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <button wire:click="editStatus({{ $status['id'] }})" 
                                    class="text-neutral-500 hover:text-green-600 dark:hover:text-green-400 transition-colors p-1" 
                                    title="Edit">
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                                </button>
                                @if(!$status['is_protected'])
                                    @if($confirmingStatusDelete === $status['id'])
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
                                        <button wire:click="confirmDeleteStatus({{ $status['id'] }})" 
                                            class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                            title="Delete">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    @endif
                                @else
                                    <span class="text-neutral-300 p-1" title="Protected items cannot be deleted">
                                        <x-heroicon-o-shield-check class="h-4 w-4" />
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-3 border-t border-neutral-200 dark:border-neutral-600">
                            <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort Order: {{ $status['sort_order'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Future Implementation Notice --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-medium mb-1">Database Integration Pending</p>
                <p>Hardware types and statuses are currently stored in memory. When database lookup tables are implemented, changes will be persisted and used throughout the organization hardware management system.</p>
            </div>
        </div>
    </div>
</div>

{{-- Hardware Type Modal --}}
@if($showTypeModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeTypeModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveType">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $typeEditMode ? 'Edit Hardware Type' : 'Add New Hardware Type' }}
                        </h3>
                        <button type="button" wire:click="closeTypeModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model.live="typeForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('typeForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Slug *</label>
                            <input type="text" wire:model="typeForm.slug" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent font-mono text-sm"
                                   placeholder="server">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Unique identifier (lowercase, underscores only)</p>
                            @error('typeForm.slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="typeForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('typeForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Protected</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Prevent deletion of this hardware type</span>
                            </span>
                            <button type="button" wire:click="$toggle('typeForm.is_protected')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $typeForm['is_protected'] ? 'bg-orange-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Protected</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $typeForm['is_protected'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $typeEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeTypeModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Hardware Status Modal --}}
@if($showStatusModal)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeStatusModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveStatus">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $statusEditMode ? 'Edit Hardware Status' : 'Add New Hardware Status' }}
                        </h3>
                        <button type="button" wire:click="closeStatusModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model.live="statusForm.name" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('statusForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Slug *</label>
                            <input type="text" wire:model="statusForm.slug" required
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent font-mono text-sm"
                                   placeholder="active">
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Unique identifier (lowercase, underscores only)</p>
                            @error('statusForm.slug') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="statusForm.sort_order" min="0"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                            @error('statusForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Protected</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Prevent deletion of this hardware status</span>
                            </span>
                            <button type="button" wire:click="$toggle('statusForm.is_protected')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $statusForm['is_protected'] ? 'bg-orange-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Protected</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $statusForm['is_protected'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        {{ $statusEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeStatusModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif