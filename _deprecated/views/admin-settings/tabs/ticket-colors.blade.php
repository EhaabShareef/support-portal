<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Ticket Colors</h3>
        <button wire:click="saveTicketColors" 
            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
            <x-heroicon-o-check class="h-4 w-4 mr-2" />
            Save Colors
        </button>
    </div>

    {{-- Status Colors --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-medium text-neutral-700 dark:text-neutral-300">Status Colors</h4>
            <button wire:click="confirmResetColors('status')" 
                class="text-sm text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                Reset to Defaults
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->ticketStatuses as $status)
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $status['label'] }}</span>
                        <div class="w-6 h-6 rounded border border-neutral-300" 
                             style="background-color: {{ $statusColors[$status['value']] ?? '#6B7280' }}"></div>
                    </div>
                    <select wire:change="updateStatusColor('{{ $status['value'] }}', $event.target.value)" 
                            class="w-full text-sm border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md">
                        @foreach($this->availableColors as $colorName => $colorValue)
                            <option value="{{ $colorValue }}" 
                                    {{ ($statusColors[$status['value']] ?? '') === $colorValue ? 'selected' : '' }}>
                                {{ $colorName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Priority Colors --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h4 class="text-md font-medium text-neutral-700 dark:text-neutral-300">Priority Colors</h4>
            <button wire:click="confirmResetColors('priority')" 
                class="text-sm text-neutral-500 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                Reset to Defaults
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($this->ticketPriorities as $priority)
                <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $priority['label'] }}</span>
                        <div class="w-6 h-6 rounded border border-neutral-300" 
                             style="background-color: {{ $priorityColors[$priority['value']] ?? '#6B7280' }}"></div>
                    </div>
                    <select wire:change="updatePriorityColor('{{ $priority['value'] }}', $event.target.value)" 
                            class="w-full text-sm border-neutral-300 dark:border-neutral-600 dark:bg-neutral-700 rounded-md">
                        @foreach($this->availableColors as $colorName => $colorValue)
                            <option value="{{ $colorValue }}" 
                                    {{ ($priorityColors[$priority['value']] ?? '') === $colorValue ? 'selected' : '' }}>
                                {{ $colorName }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Reset Confirmation Modal --}}
    @if($showColorResetConfirm)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-neutral-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="cancelResetColors"></div>
                <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-neutral-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/40 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-neutral-900 dark:text-neutral-100">Reset {{ ucfirst($colorResetType) }} Colors</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                        Are you sure you want to reset {{ $colorResetType }} colors to their default values? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-neutral-50 dark:bg-neutral-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="resetColorsToDefaults" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reset
                        </button>
                        <button wire:click="cancelResetColors" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-neutral-300 dark:border-neutral-600 shadow-sm px-4 py-2 bg-white dark:bg-neutral-700 text-base font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>