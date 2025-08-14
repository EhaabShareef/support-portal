<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="flex text-xl items-center font-semibold text-neutral-800 dark:text-neutral-100">
            <x-heroicon-o-cpu-chip class="inline h-8 w-8 mr-2" />
            Manage Hardware – {{ $organization->name }}
        </h1>

        <div class="flex items-center space-x-2">
            <a href="{{ route('organizations.show', ['organization' => $organization->id]) }}"
                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-left class="inline h-4 w-4 mr-1" /> Back
            </a>

            <a href="{{ route('organizations.hardware.create', ['organization' => $organization->id]) }}" 
               class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-all duration-200">
                <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" /> New Hardware
            </a>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-900 px-4 py-2 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- Form --}}
    <div x-data="{ open: @entangle('showForm') }" 
         x-show="open" 
         x-transition:enter="transition ease-out duration-300" 
         x-transition:enter-start="opacity-0 transform -translate-y-4" 
         x-transition:enter-end="opacity-100 transform translate-y-0" 
         x-transition:leave="transition ease-in duration-200" 
         x-transition:leave-start="opacity-100 transform translate-y-0" 
         x-transition:leave-end="opacity-0 transform -translate-y-4"
         style="display: none;"
         class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md space-y-4">
        <div class="p-6">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                    {{ $editingHardware ? 'Edit Hardware' : 'Create New Hardware' }}
                </h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    {{ $editingHardware ? 'Update hardware information' : 'Create a new hardware asset for this organization' }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">
                {{-- Asset Tag & Hardware Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="asset_tag" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Asset Tag
                        </label>
                        <input type="text" 
                               wire:model.defer="form.asset_tag" 
                               id="asset_tag"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.asset_tag') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="hardware_type_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Hardware Type *
                        </label>
                        <select wire:model.defer="form.hardware_type_id" 
                                id="hardware_type_id"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                            <option value="">Select Hardware Type</option>
                            @foreach($hardwareTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        @error('form.hardware_type_id') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Brand & Model --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="brand" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Brand
                        </label>
                        <input type="text" 
                               wire:model.defer="form.brand" 
                               id="brand"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.brand') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="model" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Model
                        </label>
                        <input type="text" 
                               wire:model.defer="form.model" 
                               id="model"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.model') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Serial Number --}}
                <div>
                    <label for="serial_number" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Serial Number
                    </label>
                    <input type="text" 
                           wire:model.defer="form.serial_number" 
                           id="serial_number"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @error('form.serial_number') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Contract & Location --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="contract_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Associated Contract
                        </label>
                        <select wire:model.defer="form.contract_id" 
                                id="contract_id"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                            <option value="">Select Contract (Optional)</option>
                            @foreach($contracts as $contract)
                                <option value="{{ $contract->id }}">{{ $contract->contract_number }} ({{ ucfirst($contract->type) }})</option>
                            @endforeach
                        </select>
                        @error('form.contract_id') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                        @if($contracts->count() === 0)
                            <p class="text-amber-600 text-xs mt-1">No active hardware contracts found.</p>
                        @endif
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Location <span class="text-xs text-neutral-500">(Read-only)</span>
                        </label>
                        <input type="text" 
                               wire:model.defer="form.location" 
                               id="location"
                               readonly
                               placeholder="e.g., Office A, Floor 2, Room 201"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-not-allowed">
                        @error('form.location') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Purchase Date --}}
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Purchase Date
                    </label>
                    <input type="date" 
                           wire:model.defer="form.purchase_date" 
                           id="purchase_date"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @error('form.purchase_date') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Maintenance Information (Read-only) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="last_maintenance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Last Maintenance <span class="text-xs text-neutral-500">(Read-only)</span>
                        </label>
                        <input type="date" 
                               wire:model.defer="form.last_maintenance" 
                               id="last_maintenance"
                               readonly
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-not-allowed">
                        @error('form.last_maintenance') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="next_maintenance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Next Maintenance <span class="text-xs text-neutral-500">(Read-only)</span>
                        </label>
                        <input type="date" 
                               wire:model.defer="form.next_maintenance" 
                               id="next_maintenance"
                               readonly
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-neutral-100 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-400 cursor-not-allowed">
                        @error('form.next_maintenance') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Remarks --}}
                <div>
                    <label for="remarks" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Remarks
                    </label>
                    <textarea wire:model.defer="form.remarks" 
                              id="remarks"
                              rows="3"
                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                              placeholder="Additional notes or remarks..."></textarea>
                    @error('form.remarks') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <button type="button" 
                            wire:click="cancel"
                            class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        {{ $editingHardware ? 'Update Hardware' : 'Create Hardware' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md p-4">
        <div class="flex flex-wrap gap-4 items-end">
            {{-- Contract Filter --}}
            <div class="flex-1 min-w-48">
                <label for="filterContract" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Filter by Contract
                </label>
                <select wire:model.live="filterContract" 
                        id="filterContract"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">All Contracts</option>
                    @foreach($contracts as $contract)
                        <option value="{{ $contract->id }}">{{ $contract->contract_number }} - {{ ucfirst($contract->type) }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Oracle Filter --}}
            <div class="min-w-32">
                <label for="filterIsOracle" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Oracle Contracts
                </label>
                <select wire:model.live="filterIsOracle" 
                        id="filterIsOracle"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">All</option>
                    <option value="1">Oracle Only</option>
                    <option value="0">Non-Oracle</option>
                </select>
            </div>

            {{-- Clear Filters --}}
            @if($filterContract || $filterIsOracle !== null)
            <div>
                <button wire:click="$set('filterContract', ''); $set('filterIsOracle', null)" 
                        class="px-4 py-2 bg-neutral-500 hover:bg-neutral-600 text-white text-sm rounded-md transition-colors duration-200">
                    Clear Filters
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Hardware List --}}
    <div class="space-y-6">
        @forelse ($groupedHardware as $contractName => $hardwareList)
            {{-- Contract Group --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-neutral-200 dark:border-neutral-700">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-document-text class="h-5 w-5 text-neutral-600 dark:text-neutral-400" />
                        <h2 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">{{ $contractName }}</h2>
                        <span class="text-sm text-neutral-500 dark:text-neutral-400">({{ $hardwareList->count() }} items)</span>
                    </div>
                    
                    {{-- Contract Management Button --}}
                    @if($contractName !== 'No Contract Assigned')
                        @php
                            $contractId = $hardwareList->first()->contract_id ?? null;
                        @endphp
                        @if($contractId)
                            <button wire:click="openContractModal({{ $contractId }})"
                                    class="p-2 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-md transition-colors duration-200"
                                    title="Manage hardware for this contract">
                                <x-heroicon-o-cog-6-tooth class="h-5 w-5" />
                            </button>
                        @endif
                    @endif
                </div>
                
                @foreach ($hardwareList as $hardware)
            <div wire:key="hardware-{{ $hardware->id }}"
                 class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/10 space-y-2 hover:bg-white/15 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                {{ $hardware->asset_tag ?: ($hardware->brand . ' ' . $hardware->model) }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                Active
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-cpu-chip class="h-4 w-4" />
                                <span>{{ $hardware->type?->name ?? ucfirst($hardware->hardware_type) }}</span>
                            </div>
                            
                            {{-- Show quantity if available (wizard-created) --}}
                            @if($hardware->quantity)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-numbered-list class="h-4 w-4" />
                                <span>Qty: {{ $hardware->quantity }}</span>
                            </div>
                            @endif
                            
                            {{-- Show serial info - either legacy single serial or new serial count --}}
                            @if($hardware->serial_number)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-hashtag class="h-4 w-4" />
                                <span>{{ $hardware->serial_number }}</span>
                            </div>
                            @elseif($hardware->serial_required && $hardware->serials)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-hashtag class="h-4 w-4" />
                                <span>Serials: {{ $hardware->serials->count() }}/{{ $hardware->quantity }}</span>
                            </div>
                            @endif
                            
                            @if($hardware->location)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-map-pin class="h-4 w-4" />
                                <span>{{ $hardware->location }}</span>
                            </div>
                            @endif
                            
                            @if($hardware->purchase_date)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-calendar class="h-4 w-4" />
                                <span>{{ $hardware->purchase_date->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                        
                        @if($hardware->contract)
                            <div class="mt-2 flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400">
                                <x-heroicon-o-document-text class="h-4 w-4" />
                                <span>Contract: {{ $hardware->contract->contract_number }}</span>
                            </div>
                        @endif
                        
                    </div>
                    
                    <div class="flex items-center gap-2 ml-4">
                        @can('hardware.update')
                        <button wire:click="edit({{ $hardware->id }})" 
                                class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-md transition-colors duration-200">
                            <x-heroicon-o-pencil class="h-4 w-4" />
                        </button>
                        @endcan
                        
                        @can('hardware.delete')
                        <button wire:click="confirmDelete({{ $hardware->id }})" 
                                class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors duration-200">
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                        @endcan
                    </div>
                </div>
                
                @if ($deleteId === $hardware->id)
                    <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Delete Hardware</h4>
                                <p class="text-sm text-red-600 dark:text-red-400">Are you sure you want to delete this hardware? This action cannot be undone.</p>
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <button wire:click="delete"
                                        class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                    Delete
                                </button>
                                <button wire:click="$set('deleteId', null)"
                                        class="px-3 py-1.5 bg-neutral-300 dark:bg-neutral-600 hover:bg-neutral-400 dark:hover:bg-neutral-500 text-neutral-800 dark:text-neutral-200 text-sm font-medium rounded-md transition-colors duration-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-12">
                <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by adding your first hardware asset.</p>
                <div class="mt-6">
                    <a href="{{ route('organizations.hardware.create', ['organization' => $organization->id]) }}"
                       class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        Add Hardware
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Contract Hardware Management Modal --}}
    <div x-data="{ open: @entangle('showContractModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" 
                 @click="open = false">
                <div class="absolute inset-0 bg-neutral-500 opacity-75"></div>
            </div>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                        Manage Contract Hardware
                    </h3>
                    <button wire:click="closeContractModal" 
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Add Hardware Button --}}
                    <div class="flex justify-end">
                        <button wire:click="addHardwareToContract"
                                class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-colors duration-200">
                            <x-heroicon-o-plus class="inline h-4 w-4 mr-1" />
                            Add More Hardware
                        </button>
                    </div>

                    {{-- Contract Hardware List --}}
                    @if(!empty($contractHardware))
                        <div class="space-y-3">
                            @foreach($contractHardware as $hardware)
                                <div class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-700/50 border border-neutral-200 dark:border-neutral-600 rounded-lg">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ $hardware['type']['name'] ?? ucfirst($hardware['hardware_type']) }}
                                            </span>
                                            @if($hardware['serial_required'] && !empty($hardware['serials']))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300">
                                                    {{ count($hardware['serials']) }}/{{ $hardware['quantity'] }} Serials
                                                </span>
                                            @elseif($hardware['serial_required'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                                                    Serials Required
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                            <div class="flex flex-wrap gap-4">
                                                @if($hardware['brand'])
                                                    <span><strong>Brand:</strong> {{ $hardware['brand'] }}</span>
                                                @endif
                                                @if($hardware['model'])
                                                    <span><strong>Model:</strong> {{ $hardware['model'] }}</span>
                                                @endif
                                                <span><strong>Quantity:</strong> {{ $hardware['quantity'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 ml-4">
                                        @if($hardware['serial_required'])
                                            <button wire:click="openSerialModal({{ $hardware['id'] }})"
                                                    class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors duration-200">
                                                <x-heroicon-o-hashtag class="h-4 w-4" />
                                            </button>
                                        @endif
                                        <button wire:click="edit({{ $hardware['id'] }})"
                                                class="p-2 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-600 rounded-md transition-colors duration-200">
                                            <x-heroicon-o-pencil class="h-4 w-4" />
                                        </button>
                                        <button wire:click="confirmDelete({{ $hardware['id'] }})"
                                                class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors duration-200">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400" />
                            <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
                            <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Add hardware to this contract.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Serial Number Management Modal --}}
    <div x-data="{ open: @entangle('showSerialModal') }" 
         x-show="open" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 transition-opacity" 
                 @click="open = false">
                <div class="absolute inset-0 bg-neutral-500 opacity-75"></div>
            </div>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
                
                @if($editingHardwareForSerial)
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                                Manage Serial Numbers
                            </h3>
                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                {{ $editingHardwareForSerial->type?->name ?? ucfirst($editingHardwareForSerial->hardware_type) }}
                                @if($editingHardwareForSerial->brand || $editingHardwareForSerial->model)
                                    - {{ $editingHardwareForSerial->brand }} {{ $editingHardwareForSerial->model }}
                                @endif
                            </p>
                        </div>
                        <button wire:click="closeSerialModal" 
                                class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>

                    <livewire:hardware-serial-manager 
                        :hardware-id="$editingHardwareForSerial->id" 
                        :target-count="$editingHardwareForSerial->quantity"
                        :key="'serial-manager-' . $editingHardwareForSerial->id" />
                @endif
            </div>
        </div>
    </div>
</div>