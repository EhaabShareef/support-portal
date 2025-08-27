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
                    @else
                        {{-- Contract Assignment Button for Unassigned Hardware --}}
                        <button wire:click="openContractAssignmentModal"
                                class="p-2 text-neutral-600 dark:text-neutral-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors duration-200"
                                title="Assign to contract">
                            <x-heroicon-o-document-plus class="h-5 w-5" />
                        </button>
                    @endif
                </div>
                
                {{-- Hardware Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($hardwareList as $hardware)
                        <div wire:key="hardware-{{ $hardware->id }}"
                             class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-3 shadow-md hover:bg-white/15 transition-all duration-200 relative">
                            
                            {{-- Hardware Info --}}
                            <div class="mb-3">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100 truncate pr-2">
                                        {{ $hardware->asset_tag ?: ($hardware->brand . ' ' . $hardware->model) }}
                                    </h3>
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 ml-1 flex-shrink-0">
                                        Active
                                    </span>
                                </div>
                                
                                <div class="space-y-1 text-xs text-neutral-600 dark:text-neutral-400">
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-o-cpu-chip class="h-3 w-3 flex-shrink-0" />
                                        <span class="truncate">{{ $hardware->type?->name ?? ucfirst($hardware->hardware_type) }}</span>
                                    </div>
                                    
                                    @if($hardware->brand && $hardware->model)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-tag class="h-3 w-3 flex-shrink-0" />
                                            <span class="truncate">{{ $hardware->brand }} {{ $hardware->model }}</span>
                                        </div>
                                    @endif
                                    
                                    @if($hardware->quantity)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-numbered-list class="h-3 w-3 flex-shrink-0" />
                                            <span>Qty: {{ $hardware->quantity }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Serial Status with Visual Indicator --}}
                                    @if($hardware->serial_number)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-hashtag class="h-3 w-3 flex-shrink-0 text-green-600 dark:text-green-400" />
                                            <span class="truncate font-mono">{{ $hardware->serial_number }}</span>
                                        </div>
                                    @elseif($hardware->serial_required && $hardware->serials && $hardware->serials->count() > 0)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-hashtag class="h-3 w-3 flex-shrink-0 {{ $hardware->serials->count() == $hardware->quantity ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}" />
                                            <span class="{{ $hardware->serials->count() == $hardware->quantity ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400' }}">
                                                Serials: {{ $hardware->serials->count() }}/{{ $hardware->quantity }}
                                            </span>
                                        </div>
                                    @elseif($hardware->serial_required)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-exclamation-triangle class="h-3 w-3 flex-shrink-0 text-red-600 dark:text-red-400" />
                                            <span class="text-red-600 dark:text-red-400">Serials Missing</span>
                                        </div>
                                    @endif
                                    
                                    @if($hardware->purchase_date)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-o-calendar class="h-3 w-3 flex-shrink-0" />
                                            <span>{{ $hardware->purchase_date->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
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
                                                    class="p-2 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors duration-200"
                                                    title="Manage Serials">
                                                <x-heroicon-o-hashtag class="h-4 w-4" />
                                            </button>
                                        @endif
                                        <button wire:click="openEditModal({{ $hardware['id'] }})"
                                                class="p-2 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-neutral-600 rounded-md transition-colors duration-200"
                                                title="Edit Hardware">
                                            <x-heroicon-o-pencil class="h-4 w-4" />
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

    {{-- Contract Assignment Modal --}}
    <div x-data="{ open: @entangle('showContractAssignmentModal') }" 
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
                 class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                        Assign to Contract
                    </h3>
                    <button wire:click="closeContractAssignmentModal" 
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <div class="space-y-6">
                    {{-- Hardware Selection --}}
                    @php
                        $unassignedHardware = $groupedHardware['No Contract Assigned'] ?? collect();
                    @endphp
                    
                    @if($unassignedHardware->count() > 0)
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    Select Hardware to Assign ({{ count($selectedHardwareIds) }}/{{ $unassignedHardware->count() }})
                                </h4>
                                <div class="flex gap-2">
                                    <button wire:click="selectAllUnassigned"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        Select All
                                    </button>
                                    <span class="text-xs text-neutral-400">•</span>
                                    <button wire:click="deselectAll"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                        Deselect All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar scrollbar-on-hover border border-neutral-200 dark:border-neutral-600 rounded-lg p-3">
                                @foreach($unassignedHardware as $hardware)
                                    <label class="flex items-center p-2 hover:bg-neutral-50 dark:hover:bg-neutral-700 rounded cursor-pointer">
                                        <input type="checkbox"
                                               wire:model.live="selectedHardwareIds"
                                               value="{{ $hardware->id }}"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-neutral-300 dark:border-neutral-600 rounded">
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                    {{ $hardware->asset_tag ?: ($hardware->brand . ' ' . $hardware->model) }}
                                                </span>
                                                @if($hardware->quantity > 1)
                                                    <span class="text-xs bg-neutral-100 dark:bg-neutral-700 px-2 py-0.5 rounded">
                                                        Qty: {{ $hardware->quantity }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-neutral-600 dark:text-neutral-400">
                                                {{ $hardware->type?->name ?? ucfirst($hardware->hardware_type) }}
                                                @if($hardware->brand || $hardware->model)
                                                    • {{ $hardware->brand }} {{ $hardware->model }}
                                                @endif
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Contract Selection --}}
                    <div>
                        <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-3">
                            Select Contract
                        </h4>
                        <div class="space-y-2">
                            @foreach($contracts as $contract)
                                <button wire:click="assignToContract({{ $contract->id }})"
                                        class="w-full text-left p-3 border border-neutral-200 dark:border-neutral-600 rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors duration-200 {{ empty($selectedHardwareIds) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ empty($selectedHardwareIds) ? 'disabled' : '' }}>
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ $contract->contract_number }}
                                            </h4>
                                            <p class="text-sm text-neutral-600 dark:text-neutral-400">
                                                {{ ucfirst($contract->type) }} Contract
                                                @if($contract->is_oracle)
                                                    • Oracle
                                                @endif
                                            </p>
                                        </div>
                                        <x-heroicon-o-chevron-right class="h-5 w-5 text-neutral-400" />
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if(empty($selectedHardwareIds))
                        <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
                            <p class="text-sm text-orange-800 dark:text-orange-200">
                                Please select at least one hardware item to assign to a contract.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Hardware Edit Modal --}}
    <div x-data="{ open: @entangle('showEditModal') }" 
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
                 class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                        Edit Hardware
                    </h3>
                    <button wire:click="closeEditModal" 
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                @if($editingHardware)
                    <form wire:submit="updateHardware" class="space-y-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                    Hardware Type
                                </label>
                                <div class="px-3 py-2 bg-neutral-100 dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md text-neutral-600 dark:text-neutral-400">
                                    {{ $editingHardware->type?->name ?? ucfirst($editingHardware->hardware_type) }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_brand" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Brand
                                    </label>
                                    <input type="text" 
                                           wire:model="editForm.brand" 
                                           id="edit_brand"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                                    @error('editForm.brand') 
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div>
                                    <label for="edit_model" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Model
                                    </label>
                                    <input type="text" 
                                           wire:model="editForm.model" 
                                           id="edit_model"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                                    @error('editForm.model') 
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="edit_quantity" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Quantity *
                                    </label>
                                    <input type="number" 
                                           wire:model="editForm.quantity" 
                                           id="edit_quantity"
                                           min="1"
                                           max="1000"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                                    @error('editForm.quantity') 
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        Serial Tracking
                                    </label>
                                    <div class="flex items-center px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm bg-white dark:bg-neutral-700 h-10">
                                        <input type="checkbox"
                                               wire:model="editForm.serial_required"
                                               id="edit_serial_required"
                                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-neutral-300 dark:border-neutral-600 rounded">
                                        <label for="edit_serial_required" class="ml-3 text-sm text-neutral-900 dark:text-neutral-100">
                                            Requires Serial Numbers
                                        </label>
                                    </div>
                                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                        Enable if this hardware needs individual serial tracking
                                    </p>
                                    @error('editForm.serial_required') 
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                            <button type="button" 
                                    wire:click="closeEditModal"
                                    class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                                Update Hardware
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>