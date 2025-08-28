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
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                     x-show="show" x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave="ease-in duration-200" 
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        {{-- Modal Header --}}
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Split Ticket
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
                            {{-- Message Selection --}}
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Split from Message
                                </label>
                                <select wire:model.live="startMessageId" 
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                    <option value="">Select a message to split from...</option>
                                    @foreach($messagesList as $m)
                                        <option value="{{ $m['id'] }}">{{ $m['preview'] }}</option>
                                    @endforeach
                                </select>
                                @error('startMessageId') 
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Options --}}
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-x-circle class="h-5 w-5 text-neutral-400" />
                                        <div>
                                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                                Close Original Ticket
                                            </label>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                Automatically close the original ticket after splitting
                                            </p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model="closeOriginal" 
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-neutral-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-neutral-600 peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>

                                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                    <div class="flex items-center space-x-3">
                                        <x-heroicon-o-document-text class="h-5 w-5 text-neutral-400" />
                                        <div>
                                            <label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                                Copy Notes
                                            </label>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                                Copy non-internal notes to the new ticket
                                            </p>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" 
                                               wire:model="copyNotes" 
                                               class="sr-only peer">
                                        <div class="w-11 h-6 bg-neutral-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-neutral-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-neutral-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-neutral-600 peer-checked:bg-indigo-600"></div>
                                    </label>
                                </div>
                            </div>

                            {{-- Warning Message --}}
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                <div class="flex">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" />
                                    <div class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <p class="font-medium">This action cannot be undone</p>
                                        <p>Splitting will create a new ticket with selected messages and move them from the original ticket.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="split" 
                                @if(!$startMessageId) disabled @endif
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove class="inline-flex items-center">
                                <x-heroicon-o-scissors class="h-4 w-4 mr-2" />
                                Split Ticket
                            </span>
                            <span wire:loading class="inline-flex items-center">
                                <x-heroicon-o-arrow-path class="h-4 w-4 mr-2 animate-spin" />
                                Splitting...
                            </span>
                        </button>
                        <button wire:click="toggle"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
