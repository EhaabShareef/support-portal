<div>
    @if($show)
        {{-- Modal Backdrop --}}
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('show') }" x-show="show" x-cloak>
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
                     x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     wire:click="toggle"></div>

                {{-- Modal Content --}}
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                     x-show="show" x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <form wire:submit.prevent="addNote">
                        {{-- Modal Header --}}
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    Add Note
                                </h3>
                                <button type="button" wire:click="toggle" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                    <x-heroicon-o-x-mark class="h-6 w-6" />
                                </button>
                            </div>

                            {{-- Ticket Info --}}
                            <div class="mb-4 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                    <div class="font-medium">Ticket #{{ $ticket->ticket_number }}</div>
                                    <div class="truncate">{{ $ticket->subject }}</div>
                                </div>
                            </div>

                            {{-- Form Fields --}}
                            <div class="space-y-4">
                                {{-- Note Content --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Note Content
                                    </label>
                                    <textarea wire:model="note" 
                                              rows="4"
                                              placeholder="Add your note..."
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent"></textarea>
                                </div>

                                {{-- Color Selection --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Note Color
                                    </label>
                                    <div class="grid grid-cols-5 gap-2">
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="noteColor" value="sky" class="sr-only">
                                            <div class="w-full h-10 bg-sky-100 dark:bg-sky-900/30 border-2 border-transparent rounded-lg flex items-center justify-center hover:border-sky-300 dark:hover:border-sky-600 transition-colors"
                                                 :class="{ 'border-sky-500 dark:border-sky-400': $wire.noteColor === 'sky' }">
                                                <div class="w-4 h-4 bg-sky-500 rounded-full"></div>
                                            </div>
                                            <span class="block text-xs text-center mt-1 text-neutral-600 dark:text-neutral-400">Sky</span>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="noteColor" value="yellow" class="sr-only">
                                            <div class="w-full h-10 bg-yellow-100 dark:bg-yellow-900/30 border-2 border-transparent rounded-lg flex items-center justify-center hover:border-yellow-300 dark:hover:border-yellow-600 transition-colors"
                                                 :class="{ 'border-yellow-500 dark:border-yellow-400': $wire.noteColor === 'yellow' }">
                                                <div class="w-4 h-4 bg-yellow-500 rounded-full"></div>
                                            </div>
                                            <span class="block text-xs text-center mt-1 text-neutral-600 dark:text-neutral-400">Yellow</span>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="noteColor" value="red" class="sr-only">
                                            <div class="w-full h-10 bg-red-100 dark:bg-red-900/30 border-2 border-transparent rounded-lg flex items-center justify-center hover:border-red-300 dark:hover:border-red-600 transition-colors"
                                                 :class="{ 'border-red-500 dark:border-red-400': $wire.noteColor === 'red' }">
                                                <div class="w-4 h-4 bg-red-500 rounded-full"></div>
                                            </div>
                                            <span class="block text-xs text-center mt-1 text-neutral-600 dark:text-neutral-400">Red</span>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="noteColor" value="green" class="sr-only">
                                            <div class="w-full h-10 bg-green-100 dark:bg-green-900/30 border-2 border-transparent rounded-lg flex items-center justify-center hover:border-green-300 dark:hover:border-green-600 transition-colors"
                                                 :class="{ 'border-green-500 dark:border-green-400': $wire.noteColor === 'green' }">
                                                <div class="w-4 h-4 bg-green-500 rounded-full"></div>
                                            </div>
                                            <span class="block text-xs text-center mt-1 text-neutral-600 dark:text-neutral-400">Green</span>
                                        </label>
                                        
                                        <label class="relative cursor-pointer">
                                            <input type="radio" wire:model="noteColor" value="purple" class="sr-only">
                                            <div class="w-full h-10 bg-purple-100 dark:bg-purple-900/30 border-2 border-transparent rounded-lg flex items-center justify-center hover:border-purple-300 dark:hover:border-purple-600 transition-colors"
                                                 :class="{ 'border-purple-500 dark:border-purple-400': $wire.noteColor === 'purple' }">
                                                <div class="w-4 h-4 bg-purple-500 rounded-full"></div>
                                            </div>
                                            <span class="block text-xs text-center mt-1 text-neutral-600 dark:text-neutral-400">Purple</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Internal Note Toggle --}}
                                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-eye-slash class="h-5 w-5 text-neutral-400" />
                                        <div>
                                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                                Internal Note
                                            </label>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                Only visible to support staff and admins
                                            </p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model="noteInternal" 
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-sky-300 dark:peer-focus:ring-sky-800 rounded-full peer dark:bg-neutral-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-neutral-600 peer-checked:bg-sky-600"></div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove class="inline-flex items-center">
                                    <x-heroicon-o-document-plus class="h-4 w-4 mr-2" />
                                    Save Note
                                </span>
                                <span wire:loading class="inline-flex items-center">
                                    <x-heroicon-o-arrow-path class="h-4 w-4 mr-2 animate-spin" />
                                    Saving...
                                </span>
                            </button>
                            <button type="button" wire:click="toggle"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
