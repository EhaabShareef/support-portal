<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <x-heroicon-o-building-office-2 class="h-6 w-6 text-neutral-800 dark:text-white" />
            <h1 class="text-2xl font-semibold text-neutral-800 dark:text-white">Organizations</h1>
        </div>

        @if($this->canCreate)
            <button wire:click="create"
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                <x-heroicon-o-plus class="h-5 w-5 mr-2" />
                New Organization
            </button>
        @endif
    </div>

    <div class="flex flex-col space-y-6 transition-all duration-300 ease-in-out">

        {{-- Inline Form --}}
        @if ($showForm)
            <div
                class="transition-all duration-300 ease-in-out transform scale-100 opacity-100 translate-y-0
               bg-white/60 dark:bg-neutral-800/60 backdrop-blur border border-neutral-200 dark:border-neutral-700 
               rounded-md p-6 space-y-4 glass-card">

                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-neutral-800 dark:text-white">
                        {{ $form['id'] ? 'Edit Organization' : 'New Organization' }}
                    </h2>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ([
        'name' => 'Name',
        'company' => 'Company',
        'company_contact' => 'Company Contact',
        'tin_no' => 'TIN No',
        'email' => 'Email',
        'phone' => 'Phone',
    ] as $field => $label)
                        <div>
                            <label for="{{ $field }}"
                                class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $label }}</label>
                            <input wire:model.defer="form.{{ $field }}" id="{{ $field }}"
                                type="{{ $field === 'email' ? 'email' : 'text' }}"
                                class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200" />
                            @error("form.$field")
                                <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Subscription Status</label>
                        <select wire:model.defer="form.subscription_status"
                            class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200">
                            <option value="trial">Trial</option>
                            <option value="active">Active</option>
                            <option value="suspended">Suspended</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        @error("form.subscription_status")
                            <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Active</label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model.defer="form.is_active"
                                class="rounded border-neutral-300 text-sky-600 shadow-sm focus:ring-sky-500 transition-all duration-200">
                            <span class="ml-2 text-sm text-neutral-800 dark:text-neutral-200">Yes</span>
                        </label>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label for="notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Notes</label>
                    <textarea wire:model.defer="form.notes" id="notes" rows="3"
                        class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                               rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200"
                        placeholder="Internal notes about this organization..."></textarea>
                    @error("form.notes")
                        <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showForm', false)"
                        class="px-3 py-1 text-sm rounded-md bg-neutral-300 dark:bg-neutral-700 text-neutral-800 dark:text-white hover:bg-neutral-400 dark:hover:bg-neutral-600 transition">
                        Cancel
                    </button>
                    <button wire:click="save"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition">
                        Save
                    </button>
                </div>
            </div>
        @endif

        {{-- Simple Delete Confirm (optional UI) --}}
        @if ($deleteId)
            <div
                class="glass-card p-4 flex items-center justify-between border border-red-400 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-300 rounded-xl">
                <div class="text-sm">Are you sure you want to delete this organization?</div>
                <div class="flex gap-2">
                    <button wire:click="$set('deleteId', null)"
                        class="px-3 py-1 rounded-md bg-neutral-300 dark:bg-neutral-700 text-sm text-neutral-800 dark:text-white">
                        Cancel
                    </button>
                    <button wire:click="delete"
                        class="px-3 py-1 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm">
                        Confirm Delete
                    </button>
                </div>
            </div>
        @endif

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
                 x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
                class="rounded-md border border-green-300 dark:border-green-700 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 px-4 py-3 text-sm shadow-md backdrop-blur">
                <div class="flex items-center">
                    <x-heroicon-o-check-circle class="h-5 w-5 mr-2" />
                    {{ session('message') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
                 x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
                 x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
                class="rounded-md border border-red-300 dark:border-red-700 bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 px-4 py-3 text-sm shadow-md backdrop-blur">
                <div class="flex items-center">
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                    {{ session('error') }}
                </div>
            </div>
        @endif

        {{-- Search and Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by name, company, or email..."
                    class="w-full px-4 py-2 rounded-md border border-neutral-300 dark:border-neutral-700 bg-white/60 dark:bg-neutral-800/60 text-neutral-800 dark:text-neutral-200 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none text-sm transition-all duration-200" />
            </div>
            
            <div>
                <select wire:model.live="statusFilter"
                    class="w-full px-4 py-2 rounded-md border border-neutral-300 dark:border-neutral-700 bg-white/60 dark:bg-neutral-800/60 text-neutral-800 dark:text-neutral-200 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none text-sm transition-all duration-200">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div>
                <select wire:model.live="subscriptionFilter"
                    class="w-full px-4 py-2 rounded-md border border-neutral-300 dark:border-neutral-700 bg-white/60 dark:bg-neutral-800/60 text-neutral-800 dark:text-neutral-200 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none text-sm transition-all duration-200">
                    <option value="all">All Subscriptions</option>
                    <option value="trial">Trial</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        {{-- List View --}}

        @forelse($organizations as $org)
            <div wire:key="org-{{ $org->id }}"
                class="glass-card bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1 hover:scale-[1.02]">
                
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    {{-- Organization Info --}}
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                    {{ $org->name }}
                                </h3>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $org->company }}</p>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                {{-- Status Badge --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $org->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                    {{ $org->status_label }}
                                </span>
                                
                                {{-- Subscription Badge --}}
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($org->subscription_status === 'active') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                    @elseif($org->subscription_status === 'trial') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($org->subscription_status === 'suspended') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                    @endif">
                                    {{ $org->subscription_status_label }}
                                </span>
                            </div>
                        </div>

                        {{-- Contact Info --}}
                        <div class="flex flex-wrap items-center gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-envelope class="h-4 w-4" />
                                {{ $org->email }}
                            </div>
                            @if($org->phone)
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-phone class="h-4 w-4" />
                                {{ $org->phone }}
                            </div>
                            @endif
                            <div class="flex items-center gap-1">
                                <x-heroicon-o-identification class="h-4 w-4" />
                                TIN: {{ $org->tin_no }}
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex flex-wrap items-center gap-6 text-sm">
                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-users class="h-4 w-4" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $org->users_count ?? 0 }}</span> users
                            </div>
                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-document-text class="h-4 w-4" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $org->contracts_count ?? 0 }}</span> contracts
                            </div>
                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-cpu-chip class="h-4 w-4" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $org->hardware_count ?? 0 }}</span> hardware
                            </div>
                            <div class="flex items-center gap-1 text-neutral-600 dark:text-neutral-400">
                                <x-heroicon-o-ticket class="h-4 w-4" />
                                <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $org->tickets_count ?? 0 }}</span> tickets
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 lg:ml-4">
                        {{-- View --}}
                        <a href="{{ route('organizations.show', $org) }}"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                            <x-heroicon-o-eye class="h-4 w-4 mr-1" />
                            View
                        </a>

                        {{-- Edit --}}
                        @if($this->canEdit)
                        <button wire:click="edit({{ $org->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-sky-600 dark:text-sky-400 hover:text-sky-800 dark:hover:text-sky-300 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-md transition-all duration-200"
                            title="Edit">
                            <x-heroicon-o-pencil class="h-4 w-4 mr-1" />
                            Edit
                        </button>
                        @endif

                        {{-- Delete --}}
                        @if($this->canDelete)
                        <button wire:click="confirmDelete({{ $org->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-md transition-all duration-200"
                            title="Delete">
                            <x-heroicon-o-trash class="h-4 w-4 mr-1" />
                            Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-neutral-500 dark:text-neutral-400 py-8">
                No organizations found.
            </p>
        @endforelse
    </div>


    {{-- Pagination --}}
    <div class="pt-4">
        {{ $organizations->links() }}
    </div>



</div>
