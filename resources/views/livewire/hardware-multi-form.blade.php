<div class="space-y-6 p-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-100 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    {{-- Hardware Entry Form --}}
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Add Hardware Item</h3>
        
        <form wire:submit.prevent="addHardware" class="space-y-4">
            {{-- Hardware Type --}}
            <div>
                <label for="hardware_type_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Hardware Type *
                </label>
                <select wire:model="currentForm.hardware_type_id" 
                        id="hardware_type_id"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Hardware Type</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('currentForm.hardware_type_id')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Brand & Model --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="brand" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Brand
                    </label>
                    <input type="text" 
                           wire:model="currentForm.brand" 
                           id="brand"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                           placeholder="e.g., Dell, HP, Apple">
                    @error('currentForm.brand')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="model" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Model
                    </label>
                    <input type="text" 
                           wire:model="currentForm.model" 
                           id="model"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                           placeholder="e.g., OptiPlex 7090, MacBook Pro">
                    @error('currentForm.model')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Quantity & Serial Required --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Quantity *
                    </label>
                    <input type="number" 
                           wire:model="currentForm.quantity" 
                           id="quantity"
                           min="1"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    @error('currentForm.quantity')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex items-center pt-6">
                    <input type="checkbox" 
                           wire:model="currentForm.serial_required" 
                           id="serial_required" 
                           class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded">
                    <label for="serial_required" class="ml-2 text-sm text-neutral-700 dark:text-neutral-300">
                        Serial numbers required
                    </label>
                </div>
            </div>

            {{-- Remarks --}}
            <div>
                <label for="remarks" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Remarks
                </label>
                <textarea wire:model="currentForm.remarks" 
                          id="remarks"
                          rows="2"
                          class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                          placeholder="Additional notes or specifications..."></textarea>
                @error('currentForm.remarks')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            {{-- Add Hardware Button --}}
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <x-heroicon-o-plus class="inline h-4 w-4 mr-1" />
                    Add Hardware
                </button>
            </div>
        </form>
    </div>

    {{-- Added Hardware Items List --}}
    @if(!empty($hardwareItems))
        <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                    Added Hardware Items ({{ count($hardwareItems) }})
                </h3>
            </div>

            <div class="space-y-3">
                @foreach($hardwareItems as $index => $item)
                    <div class="flex items-center justify-between p-4 bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ $item['type_name'] }}
                                </span>
                                @if($item['serial_required'])
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                                        <x-heroicon-o-hashtag class="h-3 w-3 mr-1" />
                                        Serials Required
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-neutral-600 dark:text-neutral-400">
                                <div class="flex flex-wrap gap-4">
                                    @if($item['brand'])
                                        <span><strong>Brand:</strong> {{ $item['brand'] }}</span>
                                    @endif
                                    @if($item['model'])
                                        <span><strong>Model:</strong> {{ $item['model'] }}</span>
                                    @endif
                                    <span><strong>Quantity:</strong> {{ $item['quantity'] }}</span>
                                </div>
                                @if($item['remarks'])
                                    <div class="mt-1"><strong>Remarks:</strong> {{ $item['remarks'] }}</div>
                                @endif
                            </div>
                        </div>
                        <button wire:click="removeHardware({{ $index }})"
                                class="ml-4 p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors duration-200">
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between pt-6 border-t border-neutral-200 dark:border-neutral-700">
        <div class="text-sm text-neutral-600 dark:text-neutral-400">
            @if(empty($hardwareItems))
                Add at least one hardware item to continue
            @else
                {{ count($hardwareItems) }} hardware item(s) added
            @endif
        </div>
        
        <div class="flex items-center gap-3">
            @if(!empty($hardwareItems))
                <button wire:click="addAndContinue" 
                        class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    Continue to Next Step
                    <x-heroicon-o-arrow-right class="inline h-4 w-4 ml-1" />
                </button>
            @endif
        </div>
    </div>
</div>
