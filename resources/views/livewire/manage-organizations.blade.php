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

    <div class="flex flex-col space-y-6" x-data="{ showForm: @entangle('showForm') }">

        {{-- Inline Form --}}
        <div x-show="showForm" 
             x-transition:enter="transition ease-out duration-400" 
             x-transition:enter-start="opacity-0 transform -translate-y-2 scale-98" 
             x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-300" 
             x-transition:leave-start="opacity-100 transform translate-y-0 scale-100" 
             x-transition:leave-end="opacity-0 transform -translate-y-2 scale-98"
             class="bg-white/60 dark:bg-neutral-800/60 backdrop-blur border border-neutral-200 dark:border-neutral-700 
                    rounded-md p-6 space-y-4 glass-card"
             style="display: none;"
             x-cloak>

                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-neutral-800 dark:text-white">
                        {{ $form['id'] ? 'Edit Organization' : 'New Organization' }}
                    </h2>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="showForm" x-transition.delay.100ms>
                    @foreach ([
        'name' => 'Name',
        'company' => 'Company',
        'company_contact' => 'Company Contact',
        'tin_no' => 'TIN No',
        'email' => 'Email',
        'phone' => 'Phone',
    ] as $field => $label)
                        <div class="form-field-stagger">
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

                    <div class="form-field-stagger">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Active Status</label>
                            <button type="button" wire:click="$toggle('form.is_active')" 
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 {{ $form['is_active'] ? 'bg-neutral-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                                <span class="sr-only">Toggle active status</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $form['is_active'] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2 form-field-stagger">
                    <label for="notes" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Notes</label>
                    <textarea wire:model.defer="form.notes" id="notes" rows="3"
                        class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                               rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200"
                        placeholder="Internal notes about this organization..."></textarea>
                    @error("form.notes")
                        <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2" x-show="showForm" x-transition.delay.200ms>
                    <button wire:click="$set('showForm', false)"
                        class="px-3 py-1 text-sm rounded-md bg-neutral-300 dark:bg-neutral-700 text-neutral-800 dark:text-white hover:bg-neutral-400 dark:hover:bg-neutral-600 transition-all duration-200 transform hover:scale-105">
                        Cancel
                    </button>
                    <button wire:click="save"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                        Save
                    </button>
                </div>
            </div>

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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
                                        {{ $org->name }}
                                    </h3>
                                    {{-- Status Badge --}}
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $org->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        {{ $org->status_label }}
                                    </span>
                                </div>
                                <p class="text-sm text-neutral-600 dark:text-neutral-400">{{ $org->company }}</p>
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
