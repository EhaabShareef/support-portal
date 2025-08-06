<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
            {{ $isEditing ? 'Edit Contract' : 'Add New Contract' }}
        </h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            {{ $isEditing ? 'Update contract information' : 'Create a new contract for this organization' }}
        </p>
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Contract Number & Department --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="contract_number" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Contract Number *
                </label>
                <input type="text" 
                       wire:model="form.contract_number" 
                       id="contract_number"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.contract_number') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="department_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Department *
                </label>
                <select wire:model="form.department_id" 
                        id="department_id"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="">Select Department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('form.department_id') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Type & Status --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Contract Type *
                </label>
                <select wire:model="form.type" 
                        id="type"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="support">Support</option>
                    <option value="hardware">Hardware</option>
                    <option value="software">Software</option>
                    <option value="consulting">Consulting</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                @error('form.type') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Status *
                </label>
                <select wire:model="form.status" 
                        id="status"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="draft">Draft</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="terminated">Terminated</option>
                    <option value="renewed">Renewed</option>
                </select>
                @error('form.status') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Contract Value & Currency --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label for="contract_value" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Contract Value
                </label>
                <input type="number" 
                       wire:model="form.contract_value" 
                       id="contract_value"
                       step="0.01"
                       min="0"
                       placeholder="0.00"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.contract_value') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="currency" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Currency
                </label>
                <select wire:model="form.currency" 
                        id="currency"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="MVR">MVR</option>
                </select>
                @error('form.currency') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label for="start_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Start Date *
                </label>
                <input type="date" 
                       wire:model="form.start_date" 
                       id="start_date"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.start_date') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    End Date
                </label>
                <input type="date" 
                       wire:model="form.end_date" 
                       id="end_date"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.end_date') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <label for="renewal_months" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Renewal (Months)
                </label>
                <input type="number" 
                       wire:model="form.renewal_months" 
                       id="renewal_months"
                       min="1"
                       max="120"
                       placeholder="12"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.renewal_months') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        {{-- Hardware Inclusion --}}
        <div class="flex items-center">
            <input type="checkbox" 
                   wire:model="form.includes_hardware" 
                   id="includes_hardware"
                   class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded">
            <label for="includes_hardware" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                This contract includes hardware
            </label>
        </div>

        {{-- CSI Remarks --}}
        <div>
            <label for="csi_remarks" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                CSI Remarks
            </label>
            <textarea wire:model="form.csi_remarks" 
                      id="csi_remarks"
                      rows="3"
                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                      placeholder="Customer Service Index remarks..."></textarea>
            @error('form.csi_remarks') 
                <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
            @enderror
        </div>

        {{-- Terms & Conditions --}}
        <div>
            <label for="terms_conditions" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                Terms & Conditions
            </label>
            <textarea wire:model="form.terms_conditions" 
                      id="terms_conditions"
                      rows="4"
                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                      placeholder="Contract terms and conditions..."></textarea>
            @error('form.terms_conditions') 
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
                {{ $isEditing ? 'Update Contract' : 'Create Contract' }}
            </button>
        </div>
    </form>
</div>