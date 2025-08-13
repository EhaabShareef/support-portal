<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="flex text-xl items-center font-semibold text-neutral-800 dark:text-neutral-100">
            <x-heroicon-o-document-text class="inline h-8 w-8 mr-2" />
            Manage Contracts – {{ $organization->name }}
        </h1>

        <div class="flex items-center space-x-2">
            <a href="{{ route('organizations.show', ['organization' => $organization->id]) }}"
                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-left class="inline h-4 w-4 mr-1" /> Back
            </a>

            <button wire:click="create" 
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-all duration-200">
                <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" /> New Contract
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
                            {{ $editingContract ? 'Edit Contract' : 'Create New Contract' }}
                        </h3>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400">
                            {{ $editingContract ? 'Update contract information' : 'Create a new contract for this organization' }}
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

                        {{-- Oracle Details --}}
                        <div class="mt-4">
                            <div class="flex items-center">
                                <input type="checkbox"
                                       wire:model="form.is_oracle"
                                       id="is_oracle"
                                       class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded">
                                <label for="is_oracle" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                                    Oracle Contract
                                </label>
                            </div>
                            @if($form['is_oracle'])
                                <div class="mt-2">
                                    <label for="csi_number" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                        CSI Number *
                                    </label>
                                    <input type="text"
                                           wire:model="form.csi_number"
                                           id="csi_number"
                                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                                    @error('form.csi_number')
                                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif
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

                        {{-- Notes --}}
                        <div>
                            <label for="notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Notes
                            </label>
                            <textarea wire:model="form.notes"
                                      id="notes"
                                      rows="4"
                                      class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100"
                                      placeholder="Additional notes..."></textarea>
                            @error('form.notes')
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
                                {{ $editingContract ? 'Update Contract' : 'Create Contract' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

    {{-- Contract List --}}
    <div class="space-y-4">
        @forelse ($contracts as $contract)
            <div wire:key="contract-{{ $contract->id }}"
                 class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/10 space-y-2 hover:bg-white/15 transition-all duration-200">
                <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $contract->contract_number }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($contract->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300
                                    @elseif($contract->status === 'draft') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($contract->status === 'expired') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                    @endif">
                                    {{ ucfirst($contract->status) }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-building-office class="h-4 w-4" />
                                    <span>{{ $contract->department->name ?? 'No Department' }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-tag class="h-4 w-4" />
                                    <span>{{ ucfirst($contract->type) }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-calendar class="h-4 w-4" />
                                    <span>{{ $contract->start_date->format('M d, Y') }} - {{ $contract->end_date ? $contract->end_date->format('M d, Y') : 'Ongoing' }}</span>
                                </div>
                            </div>
                            
                            @if($contract->includes_hardware)
                                <div class="mt-2 flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                                    <x-heroicon-o-cpu-chip class="h-4 w-4" />
                                    <span>Hardware Included</span>
                                </div>
                            @endif
                            
                            @if($contract->csi_remarks)
                                <div class="mt-2 text-sm text-neutral-600 dark:text-neutral-400">
                                    <span class="font-medium">CSI:</span> {{ $contract->csi_remarks }}
                                </div>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 ml-4">
                            @can('contracts.update')
                            <button wire:click="edit({{ $contract->id }})" 
                                    class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-md transition-colors duration-200">
                                <x-heroicon-o-pencil class="h-4 w-4" />
                            </button>
                            @endcan
                            
                            @can('contracts.delete')
                            <button wire:click="confirmDelete({{ $contract->id }})" 
                                    class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors duration-200">
                                <x-heroicon-o-trash class="h-4 w-4" />
                            </button>
                            @endcan
                        </div>
                    </div>
                    
                    @if ($deleteId === $contract->id)
                        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Delete Contract</h4>
                                    <p class="text-sm text-red-600 dark:text-red-400">Are you sure you want to delete this contract? This action cannot be undone.</p>
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
                    <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                    <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No contracts found</h3>
                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first contract.</p>
                    <div class="mt-6">
                        <button wire:click="create" 
                                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                            Create Contract
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

    {{-- Pagination --}}
    @if($contracts->hasPages())
        <div class="pt-4">
            {{ $contracts->links('vendor.pagination.tailwind') }}
        </div>
    @endif
    </div>
</div>
