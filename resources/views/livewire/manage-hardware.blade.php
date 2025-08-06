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

            <button wire:click="create" 
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-all duration-200">
                <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" /> New Hardware
            </button>
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
                        <label for="hardware_type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Hardware Type *
                        </label>
                        <select wire:model.defer="form.hardware_type" 
                                id="hardware_type"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                            @foreach(\App\Enums\HardwareType::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.hardware_type') 
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

                {{-- Serial Number & Status --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <div>
                        <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Status *
                        </label>
                        <select wire:model.defer="form.status" 
                                id="status"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                            @foreach(\App\Enums\HardwareStatus::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('form.status') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
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
                            Location
                        </label>
                        <input type="text" 
                               wire:model.defer="form.location" 
                               id="location"
                               placeholder="e.g., Office A, Floor 2, Room 201"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.location') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Purchase Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <div>
                        <label for="purchase_price" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Purchase Price (USD)
                        </label>
                        <input type="number" 
                               wire:model.defer="form.purchase_price" 
                               id="purchase_price"
                               step="0.01"
                               min="0"
                               placeholder="0.00"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.purchase_price') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Warranty Information --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="warranty_start" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Warranty Start
                        </label>
                        <input type="date" 
                               wire:model.defer="form.warranty_start" 
                               id="warranty_start"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.warranty_start') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="warranty_expiration" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Warranty Expiration
                        </label>
                        <input type="date" 
                               wire:model.defer="form.warranty_expiration" 
                               id="warranty_expiration"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.warranty_expiration') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Specifications --}}
                <div>
                    <label for="specifications" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Specifications
                    </label>
                    <textarea wire:model.defer="form.specifications" 
                              id="specifications"
                              rows="3"
                              class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                              placeholder="CPU, RAM, Storage, etc..."></textarea>
                    @error('form.specifications') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
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

    {{-- Hardware List --}}
    <div class="space-y-4">
        @forelse ($hardwareList as $hardware)
            <div wire:key="hardware-{{ $hardware->id }}"
                 class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/10 space-y-2 hover:bg-white/15 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                {{ $hardware->asset_tag ?: ($hardware->brand . ' ' . $hardware->model) }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($hardware->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                @elseif($hardware->status === 'maintenance') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                @elseif($hardware->status === 'retired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                @endif">
                                {{ ucfirst($hardware->status) }}
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-cpu-chip class="h-4 w-4" />
                                <span>{{ ucfirst($hardware->hardware_type) }}</span>
                            </div>
                            
                            @if($hardware->serial_number)
                            <div class="flex items-center gap-2">
                                <x-heroicon-o-hashtag class="h-4 w-4" />
                                <span>{{ $hardware->serial_number }}</span>
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
                        
                        @if($hardware->specifications)
                            <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                                <span class="font-medium">Specs:</span> {{ Str::limit($hardware->specifications, 100) }}
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
        @empty
            <div class="text-center py-12">
                <x-heroicon-o-cpu-chip class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No hardware found</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by adding your first hardware asset.</p>
                <div class="mt-6">
                    <button wire:click="create" 
                            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        Add Hardware
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($hardwareList->hasPages())
        <div class="pt-4">
            {{ $hardwareList->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>