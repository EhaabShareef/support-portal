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
                    
                    <form wire:submit.prevent="closeTicket">
                        {{-- Modal Header --}}
                        <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                    Close Ticket
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
                                {{-- Remarks --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Closing Remarks (Optional)
                                    </label>
                                    <textarea wire:model="remarks" 
                                              rows="3"
                                              placeholder="Provide any closing remarks or summary..."
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                                </div>

                                {{-- Solution --}}
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Solution Summary (Optional)
                                    </label>
                                    <textarea wire:model="solution" 
                                              rows="3"
                                              placeholder="Describe the solution or resolution provided..."
                                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"></textarea>
                                </div>

                                {{-- Warning Message --}}
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex">
                                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-400 mr-2 flex-shrink-0 mt-0.5" />
                                        <div class="text-sm text-red-800 dark:text-red-200">
                                            <p class="font-medium">This action cannot be undone</p>
                                            <p>Closing this ticket will mark it as resolved and prevent further updates.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" 
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                <x-heroicon-o-x-circle class="h-4 w-4 mr-2" />
                                Close Ticket
                            </button>
                            <button type="button" wire:click="toggle"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
