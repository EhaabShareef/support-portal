<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-building-office-2 class="h-8 w-8" />
                    Organizations
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Manage and track client organizations</p>
            </div>

            {{-- Debug Button (temporary) --}}
            <button wire:click="debugPermissions"
                class="inline-flex items-center px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-all duration-200 mr-2">
                <x-heroicon-o-bug-ant class="h-4 w-4 mr-1" />
                Debug Perms
            </button>

            @if($this->canCreate)
                <button wire:click="create"
                    class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    New Organization
                </button>
            @else
                <div class="text-sm text-amber-600 dark:text-amber-400">
                    Cannot create: Admin role: {{ auth()->user()->hasRole('admin') ? 'Yes' : 'No' }}, 
                    Can create: {{ auth()->user()->can('organizations.create') ? 'Yes' : 'No' }}
                </div>
            @endif
        </div>
    </div>

    {{-- Primary User Warning Banner --}}
    @php
        $organizationsWithoutPrimary = $organizations->filter(function($org) {
            return !$org->hasPrimaryUser();
        });
    @endphp
    
    @if($organizationsWithoutPrimary->count() > 0)
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700/50 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200 mb-1">
                        Primary User Required
                    </h3>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mb-2">
                        The following {{ $organizationsWithoutPrimary->count() }} organization(s) do not have a primary user set. 
                        Primary users provide contact information for their organizations.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($organizationsWithoutPrimary->take(5) as $org)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                {{ $org->name }}
                            </span>
                        @endforeach
                        @if($organizationsWithoutPrimary->count() > 5)
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                +{{ $organizationsWithoutPrimary->count() - 5 }} more
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

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
                    {{-- Name Field --}}
                    <div class="form-field-stagger">
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Name</label>
                        <input wire:model.defer="form.name" id="name" type="text"
                            class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200" />
                        @error("form.name")
                            <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Company Field --}}
                    <div class="form-field-stagger">
                        <label for="company" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Company</label>
                        <input wire:model.defer="form.company" id="company" type="text"
                            class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200" />
                        @error("form.company")
                            <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Company Contact Field --}}
                    <div class="form-field-stagger">
                        <label for="company_contact" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Company Contact</label>
                        <input wire:model.defer="form.company_contact" id="company_contact" type="text"
                            class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200" />
                        @error("form.company_contact")
                            <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- TIN No Field --}}
                    <div class="form-field-stagger">
                        <label for="tin_no" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">TIN No</label>
                        <input wire:model.defer="form.tin_no" id="tin_no" type="text"
                            class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                                   rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200" />
                        @error("form.tin_no")
                            <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Subscription Status --}}
                    <div class="form-field-stagger">
                        <label for="subscription_status" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">Subscription Status</label>
                        <select wire:model.defer="form.subscription_status" id="subscription_status"
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

                    {{-- Active Status Toggle --}}
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

                {{-- Primary User Selection --}}
                <div class="md:col-span-2 form-field-stagger">
                    <label for="primary_user_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Primary User
                        <span class="text-xs text-neutral-500">(Optional - can be set later)</span>
                    </label>
                    <select wire:model.defer="form.primary_user_id" id="primary_user_id"
                        class="w-full px-4 py-2 mt-1 bg-white/60 dark:bg-neutral-800/60 border border-neutral-300 dark:border-neutral-700 
                               rounded-md text-sm text-neutral-800 dark:text-neutral-100 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition-all duration-200">
                        <option value="">Select a primary user...</option>
                        @if(isset($form['id']) && $form['id'])
                            {{-- Show existing users for this organization --}}
                            @php
                                $orgUsers = \App\Models\Organization::find($form['id'])->users ?? collect();
                            @endphp
                            @foreach($orgUsers as $user)
                                @if($user->hasRole('client'))
                                    <option value="{{ $user->id }}" {{ $user->isPrimaryForOrganization($form['id']) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endif
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-neutral-500 mt-1">
                        Primary users provide contact information for their organizations. Only client users can be primary users.
                    </p>
                    @error("form.primary_user_id")
                        <p class="text-sm text-red-600 mt-1 animate-pulse">{{ $message }}</p>
                    @enderror
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
                    <button wire:click="closeForm"
                        class="px-3 py-1 text-sm rounded-md bg-neutral-300 dark:bg-neutral-700 text-neutral-800 dark:text-white hover:bg-neutral-400 dark:hover:bg-neutral-600 transition-all duration-200 transform hover:scale-105">
                        Cancel
                    </button>
                    <button wire:click="save"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow-md">
                        {{ $form['id'] ? 'Update' : 'Create' }} Organization
                    </button>
                </div>
            </div>

        {{-- Simple Delete Confirm (optional UI) --}}
        @if ($deleteId)
            <div
                class="glass-card p-4 flex items-center justify-between border border-red-400 bg-red-50 dark:bg-red-900/40 text-red-600 dark:text-red-300 rounded-xl">
                <div class="text-sm">Are you sure you want to delete this organization?</div>
                <div class="flex gap-2">
                    <button wire:click="cancelDelete"
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

        {{-- Debug Message (temporary) --}}
        @if (session()->has('debug'))
            <div class="bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 px-4 py-3 text-sm shadow-md backdrop-blur rounded-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <x-heroicon-o-bug-ant class="h-5 w-5 mr-2" />
                        <strong>Debug Info:</strong>
                    </div>
                    <button wire:click="$set('debug', null)" class="text-purple-600 hover:text-purple-800">×</button>
                </div>
                <pre class="mt-2 text-xs overflow-auto">{{ session('debug') }}</pre>
            </div>
        @endif

        {{-- Search and Filters --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Search by name, company, or contact..."
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
                            @if($org->primaryUser)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-envelope class="h-4 w-4" />
                                    {{ $org->primaryUser->email }}
                                </div>
                                @if($org->primaryUser->phone)
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-phone class="h-4 w-4" />
                                    {{ $org->primaryUser->phone }}
                                </div>
                                @endif
                            @else
                                <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                    <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                                    <span class="text-xs">No primary user set</span>
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

                        {{-- Status Toggle --}}
                        @if($this->canEdit)
                        <button wire:click="toggleStatus({{ $org->id }})"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium {{ $org->is_active ? 'text-orange-600 dark:text-orange-400 hover:text-orange-800 dark:hover:text-orange-300 hover:bg-orange-50 dark:hover:bg-orange-900/30' : 'text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/30' }} rounded-md transition-all duration-200"
                            title="{{ $org->is_active ? 'Deactivate' : 'Activate' }}">
                            @if($org->is_active)
                                <x-heroicon-o-pause class="h-4 w-4 mr-1" />
                                Deactivate
                            @else
                                <x-heroicon-o-play class="h-4 w-4 mr-1" />
                                Activate
                            @endif
                        </button>
                        @endif

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
