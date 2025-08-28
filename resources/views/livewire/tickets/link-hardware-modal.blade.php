<div>
    @if($show)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('show') }" x-show="show" x-cloak>
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="show" 
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             wire:click="toggle"></div>

        {{-- Modal --}}
        <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
             x-show="show" x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            {{-- Header --}}
            <div class="bg-white dark:bg-neutral-800 px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        Link Hardware to Ticket
                    </h3>
                    <button wire:click="toggle" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Link hardware items from {{ $ticket->organization->name }} to this ticket
                </p>
            </div>

            {{-- Content --}}
            <div class="px-6 py-4">
                {{-- Search --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Search Hardware
                    </label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-neutral-400" />
                        <input type="text" wire:model.live.debounce.300ms="search" 
                               placeholder="Search by brand, model, serial number, or asset tag..."
                               class="pl-10 pr-4 py-2 w-full text-sm border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    </div>
                </div>

                {{-- Hardware Selection --}}
                <div class="mb-6">
                    <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                        Available Hardware
                    </h4>
                    
                    @if(empty($availableHardware))
                        <div class="text-center py-8">
                            <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400" />
                            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                @if(!empty($search))
                                    No hardware matches your search criteria.
                                @else
                                    No hardware is available for this organization.
                                @endif
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-64 overflow-y-auto">
                            @foreach($availableHardware as $hardware)
                                <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors cursor-pointer {{ $selectedHardwareId == $hardware['id'] ? 'ring-2 ring-sky-500 bg-sky-50 dark:bg-sky-900/20' : '' }}"
                                     wire:click="selectHardware({{ $hardware['id'] }})">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h5 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 truncate">
                                                {{ $hardware['brand'] }} {{ $hardware['model'] }}
                                            </h5>
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                @if($hardware['type'])
                                                    {{ $hardware['type']['name'] ?? 'Unknown Type' }}
                                                @endif
                                            </p>
                                            @if($hardware['serial_number'])
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                    S/N: {{ $hardware['serial_number'] }}
                                                </p>
                                            @endif
                                            @if($hardware['asset_tag'])
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                    Asset: {{ $hardware['asset_tag'] }}
                                                </p>
                                            @endif
                                            @if($hardware['location'])
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                    📍 {{ $hardware['location'] }}
                                                </p>
                                            @endif
                                        </div>
                                        @if($hardware['serial_required'])
                                            <div class="ml-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200">
                                                    Serial Required
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Serial Selection (if hardware requires serial) --}}
                @if($selectedHardwareId && !empty($availableSerials))
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                            Select Serial Number
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-32 overflow-y-auto">
                            @foreach($availableSerials as $serial)
                                <div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-3 hover:bg-neutral-50 dark:hover:bg-neutral-700/50 transition-colors cursor-pointer {{ $selectedSerialId == $serial['id'] ? 'ring-2 ring-sky-500 bg-sky-50 dark:bg-sky-900/20' : '' }}"
                                     wire:click="selectSerial({{ $serial['id'] }})">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ $serial['serial'] }}
                                            </p>
                                            @if($serial['notes'])
                                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 truncate">
                                                    {{ $serial['notes'] }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                {{-- Maintenance Note --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Maintenance Note (Optional)
                    </label>
                    <textarea wire:model="maintenanceNote" 
                              rows="3"
                              placeholder="Add any maintenance notes or specific details about this hardware issue..."
                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm"></textarea>
                </div>

                {{-- Currently Linked Hardware --}}
                @if($ticket->hardware->count() > 0)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-3">
                            Currently Linked Hardware
                        </h4>
                        <div class="space-y-2">
                            @foreach($ticket->hardware as $linkedHardware)
                                <div class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700/50 rounded-lg">
                                    <div class="flex-1 min-w-0">
                                        <h5 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                            {{ $linkedHardware->brand }} {{ $linkedHardware->model }}
                                        </h5>
                                        @if($linkedHardware->serial_number)
                                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                                S/N: {{ $linkedHardware->serial_number }}
                                            </p>
                                        @endif
                                        @if($linkedHardware->pivot->maintenance_note)
                                            <p class="text-xs text-neutral-600 dark:text-neutral-300 mt-1">
                                                Note: {{ $linkedHardware->pivot->maintenance_note }}
                                            </p>
                                        @endif
                                    </div>
                                    <button wire:click="unlinkHardware({{ $linkedHardware->id }})" 
                                            class="ml-3 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                            title="Unlink Hardware">
                                        <x-heroicon-o-x-mark class="h-4 w-4" />
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="bg-gray-50 dark:bg-neutral-700 px-6 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button wire:click="linkHardware" 
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-sky-600 text-base font-medium text-white hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:ml-3 sm:w-auto sm:text-sm"
                        @if(!$selectedHardwareId) disabled @endif>
                    <x-heroicon-o-link class="h-4 w-4 mr-2" />
                    Link Hardware
                </button>
                <button wire:click="toggle"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-800 text-base font-medium text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</div>
