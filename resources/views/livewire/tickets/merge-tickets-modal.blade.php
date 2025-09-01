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
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
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
                                Merge Tickets
                            </h3>
                            <button type="button" wire:click="toggle" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                                <x-heroicon-o-x-mark class="h-6 w-6" />
                            </button>
                        </div>

                        {{-- Current Ticket Info --}}
                        @if($ticket)
                        <div class="mb-4 p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                <div class="font-medium">Current Ticket #{{ $ticket->ticket_number }}</div>
                                <div class="truncate">{{ $ticket->subject }}</div>
                                <div class="text-xs mt-1">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $ticket->status === 'open' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ml-2">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                    @if($ticket->owner)
                                        <span class="ml-2 text-neutral-500">Owner: {{ $ticket->owner->name }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Form Fields --}}
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Ticket Numbers to Merge
                                </label>
                                <input type="text" 
                                       wire:model.live="ticketsInput" 
                                       placeholder="Enter ticket numbers separated by commas (e.g., TKT-000001, TKT-000002, TKT-000003)"
                                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                    Enter the ticket numbers of the tickets you want to merge into this one.
                                </p>
                            </div>

                            {{-- Validation Errors --}}
                            @if(!empty($validationErrors))
                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                    <div class="flex">
                                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-400 mr-2 flex-shrink-0 mt-0.5" />
                                        <div class="text-sm text-red-800 dark:text-red-200">
                                            <p class="font-medium mb-2">Validation Errors:</p>
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($validationErrors as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Found Tickets Preview --}}
                            @if(!empty($foundTickets))
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:bg-green-800 rounded-lg p-3">
                                    <div class="flex">
                                        <x-heroicon-o-check-circle class="h-5 w-5 text-green-400 mr-2 flex-shrink-0 mt-0.5" />
                                        <div class="text-sm text-green-800 dark:text-green-200">
                                            <p class="font-medium mb-2">Tickets to be merged:</p>
                                            <div class="space-y-2">
                                                @foreach($foundTickets as $foundTicket)
                                                    <div class="bg-white dark:bg-neutral-800 rounded p-2">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex-1">
                                                                <div class="font-medium">#{{ $foundTicket['ticket_number'] }}</div>
                                                                <div class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ $foundTicket['subject'] }}</div>
                                                                @if($foundTicket['is_merged'])
                                                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">
                                                                        Previously merged into #{{ $foundTicket['merged_into'] }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="text-xs text-neutral-500 dark:text-neutral-400 ml-2">
                                                                <div class="flex items-center space-x-2">
                                                                    <span class="px-2 py-1 rounded-full text-xs {{ $foundTicket['status'] === 'open' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                                                                        {{ ucfirst($foundTicket['status']) }}
                                                                    </span>
                                                                    <span class="px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                                        {{ ucfirst($foundTicket['priority']) }}
                                                                    </span>
                                                                </div>
                                                                <div class="text-xs mt-1">{{ $foundTicket['created_at'] }}</div>
                                                                <div class="text-xs mt-1">Owner: {{ $foundTicket['owner_name'] }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Merge Options --}}
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200">Merge Options</h4>
                                        <button type="button" wire:click="$toggle('showAdvancedOptions')" class="text-blue-600 dark:text-blue-400 text-xs hover:underline">
                                            {{ $showAdvancedOptions ? 'Hide' : 'Show' }} Advanced Options
                                        </button>
                                    </div>
                                    
                                    @if($showAdvancedOptions)
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model="preservePriority" id="preservePriority" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500">
                                                <label for="preservePriority" class="ml-2 text-sm text-blue-700 dark:text-blue-300">
                                                    Preserve current ticket's priority (otherwise use highest priority)
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model="preserveStatus" id="preserveStatus" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500">
                                                <label for="preserveStatus" class="ml-2 text-sm text-blue-700 dark:text-blue-300">
                                                    Preserve current ticket's status (otherwise use best status)
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model="combineSubjects" id="combineSubjects" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500">
                                                <label for="combineSubjects" class="ml-2 text-sm text-blue-700 dark:text-blue-300">
                                                    Combine subjects intelligently
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input type="checkbox" wire:model="preserveOwner" id="preserveOwner" class="rounded border-neutral-300 text-indigo-600 focus:ring-indigo-500">
                                                <label for="preserveOwner" class="ml-2 text-sm text-blue-700 dark:text-blue-300">
                                                    Preserve current ticket's owner (otherwise assign from merged tickets)
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- Merge History (always show when applicable) --}}
                            @if($ticket && ($ticket->is_merged_master || $ticket->is_merged))
                                <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                                    <div class="flex">
                                        <x-heroicon-o-information-circle class="h-5 w-5 text-amber-400 mr-2 flex-shrink-0 mt-0.5" />
                                        <div class="text-sm text-amber-800 dark:text-amber-200">
                                            <p class="font-medium mb-2">Merge History:</p>
                                            @if($ticket->is_merged_master)
                                                <p class="text-xs">This ticket is a merge master and can receive more merges.</p>
                                            @endif
                                            @if($ticket->is_merged)
                                                <p class="text-xs">This ticket was previously merged into #{{ $ticket->merged_into?->ticket_number ?? 'Unknown' }}.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Warning Message --}}
                            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                                <div class="flex">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" />
                                    <div class="text-sm text-yellow-800 dark:text-yellow-200">
                                        <p class="font-medium">This action cannot be undone</p>
                                        <p>Merging tickets will combine all messages and notes into this ticket and close the others.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="bg-gray-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="merge" 
                                @if(!$ticket || empty($foundTickets) || !empty($validationErrors)) disabled @endif
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <x-heroicon-o-arrows-right-left class="h-4 w-4 mr-2" />
                            Merge Tickets
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
