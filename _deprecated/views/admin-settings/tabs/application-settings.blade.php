<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Application Settings</h3>
        <button wire:click="createSetting" 
            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            New Setting
        </button>
    </div>

    {{-- Settings List --}}
    @if($this->applicationSettings->count() > 0)
        @foreach($this->applicationSettings as $group => $settings)
            <div class="mb-8">
                <h4 class="text-md font-medium text-neutral-700 dark:text-neutral-300 mb-4 capitalize border-b border-neutral-200 dark:border-neutral-700 pb-2">
                    {{ str_replace('_', ' ', $group) }} Settings
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($settings as $setting)
                        <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex-1">
                                    <h5 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm">
                                        {{ $setting->label ?: $setting->key }}
                                    </h5>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">{{ $setting->key }}</p>
                                </div>
                                <div class="flex items-center space-x-1 ml-2">
                                    <button wire:click="editSetting({{ $setting->id }})" 
                                        class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    <button wire:click="confirmDeleteSetting({{ $setting->id }})" 
                                        class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            
                            @if($setting->description)
                                <p class="text-xs text-neutral-600 dark:text-neutral-400 mb-3">{{ $setting->description }}</p>
                            @endif
                            
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                                    {{ $setting->is_public ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200' }}">
                                    {{ $setting->is_public ? 'Public' : 'Private' }}
                                </span>
                                <span class="text-xs text-neutral-500 dark:text-neutral-400 bg-neutral-100 dark:bg-neutral-700 px-2 py-1 rounded">
                                    {{ $setting->type }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="text-center py-12 text-neutral-500 dark:text-neutral-400">
            <x-heroicon-o-adjustments-horizontal class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No application settings found</p>
        </div>
    @endif

    {{-- Setting Modal --}}
    @if($showSettingModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeSettingModal"></div>
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                            {{ $settingEditMode ? 'Edit Setting' : 'Create Setting' }}
                        </h3>
                        
                        <form wire:submit.prevent="saveSetting">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Key *</label>
                                    <input type="text" wire:model="settingForm.key" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                    @error('settingForm.key') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Label *</label>
                                    <input type="text" wire:model="settingForm.label" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                    @error('settingForm.label') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Value</label>
                                    <textarea wire:model="settingForm.value" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500"></textarea>
                                    @error('settingForm.value') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Type *</label>
                                        <select wire:model="settingForm.type" 
                                            class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                            <option value="string">String</option>
                                            <option value="integer">Integer</option>
                                            <option value="float">Float</option>
                                            <option value="boolean">Boolean</option>
                                            <option value="json">JSON</option>
                                            <option value="array">Array</option>
                                        </select>
                                        @error('settingForm.type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Group *</label>
                                        <input type="text" wire:model="settingForm.group" 
                                            class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                        @error('settingForm.group') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Description</label>
                                    <textarea wire:model="settingForm.description" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500" rows="2"></textarea>
                                    @error('settingForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settingForm.is_public" class="rounded border-neutral-300 text-sky-600 shadow-sm focus:border-sky-300 focus:ring focus:ring-sky-200">
                                        <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Public</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="settingForm.is_encrypted" class="rounded border-neutral-300 text-sky-600 shadow-sm focus:border-sky-300 focus:ring focus:ring-sky-200">
                                        <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Encrypted</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center justify-end space-x-3">
                                <button type="button" wire:click="closeSettingModal" 
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm hover:bg-neutral-50 dark:hover:bg-neutral-600">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md">
                                    {{ $settingEditMode ? 'Update' : 'Create' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($confirmingSettingDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelDelete"></div>
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/40 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">Delete Setting</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        Are you sure you want to delete this setting? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="deleteSetting" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button wire:click="cancelDelete" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-700 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>