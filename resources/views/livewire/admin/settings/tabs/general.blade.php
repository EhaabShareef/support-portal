<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">General Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">App-wide settings and support hotlines</p>
        </div>
        <button wire:click="resetToDefaults" 
            wire:confirm="Are you sure you want to reset all general settings to their defaults? This cannot be undone."
            class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
            <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
            Reset to Defaults
        </button>
    </div>

    {{-- Support Hotlines Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Support Hotlines</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Numbers displayed to clients on ticket forms</p>
                </div>
                <button wire:click="openHotlineModal" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Hotline
                </button>
            </div>
        </div>
        
        <div class="p-6">
            @if(count($hotlines) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($hotlines as $key => $hotline)
                        <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h4 class="font-medium text-neutral-800 dark:text-neutral-100 text-sm flex items-center gap-2">
                                        {{ $hotline['name'] }}
                                        @if($hotline['is_active'])
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-200">
                                                Inactive
                                            </span>
                                        @endif
                                    </h4>
                                    <p class="text-sm font-mono text-sky-600 dark:text-sky-400 mt-1">{{ $hotline['number'] }}</p>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <button wire:click="editHotline('{{ $key }}')" 
                                        class="text-neutral-500 hover:text-sky-600 dark:hover:text-sky-400 transition-colors p-1" 
                                        title="Edit">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </button>
                                    <button wire:click="toggleHotlineStatus('{{ $key }}')" 
                                        class="text-neutral-500 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors p-1" 
                                        title="{{ $hotline['is_active'] ? 'Disable' : 'Enable' }}">
                                        @if($hotline['is_active'])
                                            <x-heroicon-o-eye-slash class="h-4 w-4" />
                                        @else
                                            <x-heroicon-o-eye class="h-4 w-4" />
                                        @endif
                                    </button>
                                    <button wire:click="deleteHotline('{{ $key }}')" 
                                        wire:confirm="Are you sure you want to delete this hotline?"
                                        class="text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors p-1" 
                                        title="Delete">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                            
                            <p class="text-xs text-neutral-600 dark:text-neutral-400">{{ $hotline['description'] }}</p>
                            
                            <div class="flex items-center justify-between mt-3 pt-3 border-t border-neutral-200 dark:border-neutral-600">
                                <span class="text-xs text-neutral-500 dark:text-neutral-400">Sort Order: {{ $hotline['sort_order'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-heroicon-o-phone class="mx-auto h-12 w-12 text-neutral-400" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No hotlines configured</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add your first support hotline number.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Future: Theme Settings Section --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg opacity-50">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Theme Settings</h3>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Coming soon - Configure application theme and branding</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="text-center py-8">
                <x-heroicon-o-paint-brush class="mx-auto h-8 w-8 text-neutral-400" />
                <p class="mt-2 text-sm text-neutral-500 dark:text-neutral-400">Theme customization will be available in a future update</p>
            </div>
        </div>
    </div>
</div>

{{-- Hotline Modal --}}
@if($showHotlineModal)
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showHotlineModal') }">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeHotlineModal"></div>

        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form wire:submit="saveHotline">
                <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                            {{ $hotlineEditMode ? 'Edit Hotline' : 'Add New Hotline' }}
                        </h3>
                        <button type="button" wire:click="closeHotlineModal" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Name *</label>
                            <input type="text" wire:model="hotlineForm.name" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                   placeholder="e.g., PMS Hotline" required>
                            @error('hotlineForm.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Number --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Phone Number *</label>
                            <input type="text" wire:model="hotlineForm.number" 
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                   placeholder="e.g., +1-800-PMS-HELP" required>
                            @error('hotlineForm.number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Description *</label>
                            <textarea wire:model="hotlineForm.description" rows="3"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                      placeholder="Brief description of what this hotline supports" required></textarea>
                            @error('hotlineForm.description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="hotlineForm.sort_order" min="1"
                                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                            @error('hotlineForm.sort_order') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Active Toggle --}}
                        <div class="flex items-center justify-between">
                            <span class="flex flex-grow flex-col">
                                <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">Active</span>
                                <span class="text-sm text-neutral-500 dark:text-neutral-400">Display this hotline to clients</span>
                            </span>
                            <button type="button" wire:click="$toggle('hotlineForm.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $hotlineForm['is_active'] ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Active</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $hotlineForm['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm">
                        <x-heroicon-o-check class="h-4 w-4 mr-2" />
                        {{ $hotlineEditMode ? 'Update' : 'Create' }}
                    </button>
                    <button type="button" wire:click="closeHotlineModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif