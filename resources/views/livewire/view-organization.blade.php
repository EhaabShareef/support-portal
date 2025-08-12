<div class="space-y-6">
    {{-- Header Section --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('organizations.index') }}"
                    class="inline-flex items-center text-sm text-neutral-600 dark:text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-100 hover:underline transition-colors duration-200">
                    <x-heroicon-o-arrow-left class="h-4 w-4 mr-1" /> Back to Organizations
                </a>
                <div class="hidden sm:block w-px h-6 bg-neutral-300 dark:bg-neutral-600"></div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100">{{ $organization->name }}</h1>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">{{ $organization->company }}</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $organization->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                    {{ $organization->status_label }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($organization->subscription_status === 'active') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                    @elseif($organization->subscription_status === 'trial') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                    @elseif($organization->subscription_status === 'suspended') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                    @endif">
                    {{ $organization->subscription_status_label }}
                </span>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" 
             x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2"
            class="bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 p-4 rounded-lg shadow">
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
            class="bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-200 p-4 rounded-lg shadow">
            <div class="flex items-center">
                <x-heroicon-o-exclamation-triangle class="h-5 w-5 mr-2" />
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Organization Details Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Organization Details --}}
        <div class="lg:col-span-1 space-y-6">
            <div wire:key="org-card-{{ $organization->id }}"
                class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">Organization Details</h3>
                    <button wire:click="pageRefresh"
                        class="inline-flex items-center px-3 py-1.5 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-800 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 rounded-md transition-all duration-200">
                        <x-heroicon-o-arrow-path class="h-3 w-3 mr-1" /> Refresh
                    </button>
                </div>

                <dl class="space-y-4">
                    @foreach ([
                                'company_contact' => 'Contact Person',
                                'email' => 'Email',
                                'phone' => 'Phone',
                                'tin_no' => 'TIN Number',
                            ] as $field => $label)
                        <div wire:key="field-{{ $field }}-{{ $organization->id }}">
                            <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">{{ $label }}</dt>
                            <dd class="mt-1 text-sm">
                                @if ($editMode)
                                    <input type="text" wire:model.defer="form.{{ $field }}"
                                        class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                                @else
                                    <span class="text-neutral-800 dark:text-neutral-200">{{ $organization->$field ?: 'Not provided' }}</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach

                    {{-- Subscription Status --}}
                    <div wire:key="subscription-status-{{ $organization->id }}">
                        <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Subscription</dt>
                        <dd class="mt-1 text-sm">
                            @if ($editMode)
                                <select wire:model.defer="form.subscription_status"
                                    class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200">
                                    <option value="trial">Trial</option>
                                    <option value="active">Active</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($organization->subscription_status === 'active') bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300
                                    @elseif($organization->subscription_status === 'trial') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300
                                    @elseif($organization->subscription_status === 'suspended') bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300
                                    @endif">
                                    {{ $organization->subscription_status_label }}
                                </span>
                            @endif
                        </dd>
                    </div>

                    {{-- Notes --}}
                    @if($organization->notes || $editMode)
                    <div wire:key="notes-{{ $organization->id }}">
                        <dt class="font-medium text-neutral-500 dark:text-neutral-400 text-xs uppercase tracking-wide">Notes</dt>
                        <dd class="mt-1 text-sm">
                            @if ($editMode)
                                <textarea wire:model.defer="form.notes" rows="3"
                                    class="w-full px-3 py-2 rounded-md bg-white/60 dark:bg-neutral-900/50 text-sm border border-neutral-300 dark:border-neutral-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Internal notes about this organization..."></textarea>
                            @else
                                <span class="text-neutral-600 dark:text-neutral-400">{{ $organization->notes ?: 'No notes available.' }}</span>
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>

                <div class="flex items-center gap-2 mt-6 border-t border-white/20 pt-4">
                    @if ($editMode)
                        <button wire:key="save-btn-{{ $organization->id }}" wire:click="save"
                            class="inline-flex items-center px-4 py-2 bg-sky-500 text-white hover:bg-sky-600 rounded-md text-sm transition-all duration-200 shadow-sm hover:shadow-md transform hover:scale-105">
                            <x-heroicon-o-check class="h-4 w-4 mr-1" /> Save
                        </button>
                        <button wire:key="cancel-btn-{{ $organization->id }}" wire:click="cancel"
                            class="inline-flex items-center px-4 py-2 bg-neutral-500 text-white hover:bg-neutral-600 rounded-md text-sm transition-all duration-200">
                            <x-heroicon-o-x-mark class="h-4 w-4 mr-1" /> Cancel
                        </button>
                    @else
                        @if($this->canEdit)
                            <button wire:key="edit-btn-{{ $organization->id }}" wire:click="enableEdit"
                                class="inline-flex items-center px-4 py-2 border border-sky-400 text-sky-400 hover:bg-sky-500 hover:text-white rounded-md text-sm transition-all duration-200">
                                <x-heroicon-o-pencil class="h-4 w-4 mr-1" /> Edit
                            </button>
                        @endif

                        {{-- Active/Inactive Toggle for Admins --}}
                        @if(auth()->user()->hasRole('admin') || auth()->user()->can('organizations.update'))
                            <button wire:key="toggle-btn-{{ $organization->id }}" wire:click="toggleActive"
                                class="inline-flex items-center px-4 py-2 border {{ $organization->is_active ? 'border-orange-400 text-orange-400 hover:bg-orange-500' : 'border-green-400 text-green-400 hover:bg-green-500' }} hover:text-white rounded-md text-sm transition-all duration-200">
                                @if($organization->is_active)
                                    <x-heroicon-o-pause class="h-4 w-4 mr-1" /> Deactivate
                                @else
                                    <x-heroicon-o-play class="h-4 w-4 mr-1" /> Activate
                                @endif
                            </button>
                        @endif

                        @if($this->canDelete)
                            <div class="inline-block relative">
                                @if ($confirmingDelete)
                                    <div class="flex flex-col gap-2">
                                        <span class="text-sm text-red-500" wire:key="confirm-text-{{ $organization->id }}">Delete this organization?</span>
                                        <div class="flex gap-2">
                                            <button wire:key="confirm-del-{{ $organization->id }}" wire:click="delete"
                                                class="inline-flex items-center px-3 py-2 bg-red-500 text-white hover:bg-red-600 rounded-md text-sm transition-all duration-200">
                                                <x-heroicon-o-trash class="h-4 w-4 mr-1" /> Confirm
                                            </button>
                                            <button wire:key="cancel-del-{{ $organization->id }}"
                                                wire:click="cancel"
                                                class="text-sm text-neutral-400 hover:text-neutral-200 transition-colors duration-200">Cancel</button>
                                        </div>
                                    </div>
                                @else
                                    <button wire:key="delete-btn-{{ $organization->id }}"
                                        wire:click="confirmDelete"
                                        class="inline-flex items-center px-4 py-2 border border-red-400 text-red-400 hover:bg-red-500 hover:text-white rounded-md text-sm transition-all duration-200">
                                        <x-heroicon-o-trash class="h-4 w-4 mr-1" /> Delete
                                    </button>
                                @endif
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Organization Notes --}}
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
                <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 mb-4">Organization Notes</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $organization->users()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Users</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $organization->contracts()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Contracts</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $organization->hardware()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Hardware</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $organization->tickets()->count() }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 uppercase tracking-wide">Tickets</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column - Tabbed Content --}}
        <div class="lg:col-span-2">
            <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md min-h-[800px] flex flex-col">
                {{-- Tab Navigation --}}
                <div class="border-b border-neutral-200 dark:border-neutral-700 flex-shrink-0">
                    <nav class="flex space-x-8 px-6 py-4" aria-label="Tabs">
                        @foreach([
                            'users' => ['icon' => 'users', 'label' => 'Users', 'count' => $organization->users()->count()],
                            'contracts' => ['icon' => 'document-text', 'label' => 'Contracts', 'count' => $organization->contracts()->count()],
                            'hardware' => ['icon' => 'cpu-chip', 'label' => 'Hardware', 'count' => $organization->hardware()->count()],
                            'tickets' => ['icon' => 'ticket', 'label' => 'Tickets', 'count' => $organization->tickets()->count()]
                        ] as $tab => $config)
                            <button wire:click="setActiveTab('{{ $tab }}')"
                                class="flex items-center gap-2 py-2 px-1 border-b-2 font-medium text-sm transition-all duration-200 {{ $activeTab === $tab 
                                    ? 'border-sky-500 text-sky-600 dark:text-sky-400' 
                                    : 'border-transparent text-neutral-500 dark:text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-300 hover:border-neutral-300 dark:hover:border-neutral-600' }}">
                                <x-dynamic-component :component="'heroicon-o-' . $config['icon']" class="h-4 w-4" />
                                {{ $config['label'] }}
                                <span class="ml-2 bg-neutral-100 dark:bg-neutral-700 text-neutral-600 dark:text-neutral-300 py-0.5 px-2 rounded-full text-xs">
                                    {{ $config['count'] }}
                                </span>
                            </button> 
                        @endforeach
                    </nav>
                </div>

                {{-- Tab Content - Flexible height container --}}
                <div class="p-6 flex-1 overflow-y-auto">
                    @if($activeTab === 'users')
                        @include('livewire.partials.organization.users-tab', ['organization' => $organization])
                    @elseif($activeTab === 'contracts')
                        @include('livewire.partials.organization.contracts-tab', ['organization' => $organization])
                    @elseif($activeTab === 'hardware')
                        @include('livewire.partials.organization.hardware-tab', ['organization' => $organization])
                    @elseif($activeTab === 'tickets')
                        @include('livewire.partials.organization.tickets-tab', ['organization' => $organization])
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
