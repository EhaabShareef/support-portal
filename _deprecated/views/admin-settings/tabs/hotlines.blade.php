<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Support Hotlines</h3>
        <button wire:click="openHotlineModal" 
            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
            New Hotline
        </button>
    </div>

    {{-- Hotlines List --}}
    @if(count($hotlines) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($hotlines as $key => $hotline)
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg p-6 hover:shadow-md transition-all duration-200">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h4 class="text-lg font-medium text-neutral-800 dark:text-neutral-100 mb-2">{{ $hotline['name'] }}</h4>
                            <p class="text-sm text-sky-600 dark:text-sky-400 font-medium">{{ $hotline['number'] }}</p>
                        </div>
                        <div class="flex items-center space-x-1 ml-2">
                            <button wire:click="editHotline('{{ $key }}')" 
                                class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1">
                                <x-heroicon-o-pencil-square class="h-4 w-4" />
                            </button>
                            <button wire:click="toggleHotlineStatus('{{ $key }}')" 
                                class="text-neutral-500 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors p-1">
                                @if($hotline['is_active'])
                                    <x-heroicon-o-eye class="h-4 w-4" />
                                @else
                                    <x-heroicon-o-eye-slash class="h-4 w-4" />
                                @endif
                            </button>
                            <button wire:click="deleteHotline('{{ $key }}')" 
                                class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mb-4">{{ $hotline['description'] }}</p>
                    
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-500 dark:text-neutral-400">
                            Order: {{ $hotline['sort_order'] }}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium
                            {{ $hotline['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200' }}">
                            {{ $hotline['is_active'] ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12 text-neutral-500 dark:text-neutral-400">
            <x-heroicon-o-phone class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No support hotlines configured</p>
        </div>
    @endif

    {{-- Hotline Modal --}}
    @if($showHotlineModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeHotlineModal"></div>
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                            {{ $hotlineEditMode ? 'Edit Hotline' : 'Add Hotline' }}
                        </h3>
                        
                        <form wire:submit.prevent="saveHotline">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Name *</label>
                                    <input type="text" wire:model="hotlineForm.name" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                    @error('hotlineForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Phone Number *</label>
                                    <input type="text" wire:model="hotlineForm.number" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                    @error('hotlineForm.number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Description *</label>
                                    <textarea wire:model="hotlineForm.description" 
                                        class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500" rows="3"></textarea>
                                    @error('hotlineForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Sort Order *</label>
                                        <input type="number" wire:model="hotlineForm.sort_order" min="1"
                                            class="mt-1 block w-full border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500">
                                        @error('hotlineForm.sort_order') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="flex items-center">
                                        <label class="flex items-center mt-6">
                                            <input type="checkbox" wire:model="hotlineForm.is_active" class="rounded border-neutral-300 text-sky-600 shadow-sm focus:border-sky-300 focus:ring focus:ring-sky-200">
                                            <span class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-6 flex items-center justify-end space-x-3">
                                <button type="button" wire:click="closeHotlineModal" 
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm hover:bg-neutral-50 dark:hover:bg-neutral-600">
                                    Cancel
                                </button>
                                <button type="submit" 
                                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md">
                                    {{ $hotlineEditMode ? 'Update' : 'Add' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>