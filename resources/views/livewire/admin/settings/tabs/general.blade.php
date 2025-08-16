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
                <button wire:click="addNewHotline" 
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Add Hotline
                </button>
            </div>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Add New Hotline Card (only show when adding) --}}
                @if($showAddForm)
                    <div class="bg-white dark:bg-neutral-900/50 border-2 border-dashed border-sky-300 dark:border-sky-600 rounded-lg p-4">
                        <form wire:submit="saveNewHotline">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                    <input type="text" wire:model="newHotlineForm.name" 
                                           class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                           placeholder="e.g., PMS Hotline" required>
                                    @error('newHotlineForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number *</label>
                                    <input type="text" wire:model="newHotlineForm.number" 
                                           class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                           placeholder="e.g., +1-800-PMS-HELP" required>
                                    @error('newHotlineForm.number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description *</label>
                                    <textarea wire:model="newHotlineForm.description" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                              placeholder="Brief description" required></textarea>
                                    @error('newHotlineForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex gap-2 pt-2">
                                    <button type="submit" 
                                            class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                        Save
                                    </button>
                                    <button type="button" wire:click="cancelAddHotline"
                                            class="flex-1 bg-neutral-500 hover:bg-neutral-600 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Existing Hotlines --}}
                @foreach($hotlines as $key => $hotline)
                    @if($editingKey === $key)
                        {{-- Edit Form --}}
                        <div class="bg-white dark:bg-neutral-900/50 border-2 border-orange-300 dark:border-orange-600 rounded-lg p-4">
                            <form wire:submit="saveEditHotline">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name *</label>
                                        <input type="text" wire:model="editHotlineForm.name" 
                                               class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               required>
                                        @error('editHotlineForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Phone Number *</label>
                                        <input type="text" wire:model="editHotlineForm.number" 
                                               class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                               required>
                                        @error('editHotlineForm.number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description *</label>
                                        <textarea wire:model="editHotlineForm.description" rows="2"
                                                  class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                                  required></textarea>
                                        @error('editHotlineForm.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="flex gap-2 pt-2">
                                        <button type="submit" 
                                                class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors">
                                            Update
                                        </button>
                                        <button type="button" wire:click="cancelEditHotline"
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
                                    <button wire:click="startEditHotline('{{ $key }}')" 
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
                    @endif
                @endforeach

                {{-- Empty State --}}
                @if(count($hotlines) == 0 && !$showAddForm)
                    <div class="col-span-full text-center py-12">
                        <x-heroicon-o-phone class="mx-auto h-12 w-12 text-neutral-400" />
                        <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-300">No hotlines configured</h3>
                        <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add your first support hotline number.</p>
                    </div>
                @endif
            </div>
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


