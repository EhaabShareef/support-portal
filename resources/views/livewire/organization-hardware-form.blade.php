<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
            {{ $isEditing ? 'Edit Hardware' : 'Add New Hardware' }}
        </h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            {{ $isEditing ? 'Update hardware information' : 'Create a new hardware asset for this organization' }}
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
                       wire:model="form.asset_tag" 
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
                <select wire:model="form.hardware_type" 
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
                       wire:model="form.brand" 
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
                       wire:model="form.model" 
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
                   wire:model="form.serial_number" 
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
                <select wire:model="form.contract_id" 
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
                    <p class="text-amber-600 text-xs mt-1">No active hardware contracts found. Hardware requires an active hardware contract.</p>
                @endif
            </div>

            <div>
                <label for="location" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Location
                </label>
                <input type="text" 
                       wire:model="form.location" 
                       id="location"
                       placeholder="e.g., Office A, Floor 2, Room 201"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.location') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Purchase Information --}}
        <div>
            <label for="purchase_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Purchase Date
            </label>
            <input type="date" 
                   wire:model="form.purchase_date" 
                   id="purchase_date"
                   class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
            @error('form.purchase_date') 
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
            @enderror
        </div>

        {{-- Maintenance Information --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="last_maintenance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Last Maintenance
                </label>
                <input type="date" 
                       wire:model="form.last_maintenance" 
                       id="last_maintenance"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.last_maintenance') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="next_maintenance" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Next Maintenance
                </label>
                <input type="date" 
                       wire:model="form.next_maintenance" 
                       id="next_maintenance"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
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
            <textarea wire:model="form.remarks" 
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
                {{ $isEditing ? 'Update Hardware' : 'Create Hardware' }}
            </button>
        </div>
    </form>
</div>