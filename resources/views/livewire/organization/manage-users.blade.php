<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1 class="flex text-xl items-center font-semibold text-neutral-800 dark:text-neutral-100">
            <x-heroicon-o-users class="inline h-8 w-8 mr-2" />
            Manage Users – {{ $organization->name }}
        </h1>

        <div class="flex items-center space-x-2">
            <a href="{{ route('organizations.show', ['organization' => $organization->id]) }}"
                class="px-4 py-2 bg-neutral-200 dark:bg-neutral-700 hover:bg-neutral-300 dark:hover:bg-neutral-600 text-sm text-neutral-800 dark:text-neutral-100 rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-left class="inline h-4 w-4 mr-1" /> Back
            </a>

            <button wire:click="create" 
                    class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm rounded-md transition-all duration-200">
                <x-heroicon-o-plus-circle class="inline h-4 w-4 mr-1" /> New User
            </button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-900 px-4 py-2 rounded-md text-sm">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 text-red-900 px-4 py-2 rounded-md text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- User Form --}}
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
                    {{ $editingUser ? 'Edit User' : 'Create New User' }}
                </h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    {{ $editingUser ? 'Update user information' : 'Create a new client user for this organization' }}
                </p>
            </div>

            <form wire:submit="save" class="space-y-4">
                {{-- Name & Username --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Full Name *
                        </label>
                        <input type="text" 
                               wire:model="form.name" 
                               id="name"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.name') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Username *
                        </label>
                        <input type="text" 
                               wire:model="form.username" 
                               id="username"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.username') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Email & Phone --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Email Address *
                        </label>
                        <input type="email" 
                               wire:model="form.email" 
                               id="email"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.email') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Phone Number
                        </label>
                        <input type="tel" 
                               wire:model="form.phone" 
                               id="phone"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.phone') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Password Fields --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Password {{ $editingUser ? '' : '*' }}
                        </label>
                        <input type="password" 
                               wire:model="form.password" 
                               id="password"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @if($editingUser)
                            <p class="text-xs text-neutral-500 mt-1">Leave blank to keep current password</p>
                        @endif
                        @error('form.password') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Confirm Password {{ $editingUser ? '' : '*' }}
                        </label>
                        <input type="password" 
                               wire:model="form.password_confirmation" 
                               id="password_confirmation"
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        @error('form.password_confirmation') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Status & Timezone --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" 
                               wire:model="form.is_active" 
                               id="is_active"
                               class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded">
                        <label for="is_active" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                            User is active
                        </label>
                    </div>

                    <div>
                        <label for="timezone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Timezone
                        </label>
                        <select wire:model="form.timezone" 
                                id="timezone"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                            <option value="Europe/London">London</option>
                            <option value="Europe/Paris">Paris</option>
                            <option value="Asia/Tokyo">Tokyo</option>
                            <option value="Asia/Shanghai">Shanghai</option>
                        </select>
                        @error('form.timezone') 
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                        @enderror
                    </div>
                </div>

                {{-- Organization Assignment (Read-only for existing users) --}}
                @if($editingUser)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                <p class="font-medium mb-1">Organization Assignment</p>
                                <p class="text-blue-700 dark:text-blue-300">This user is currently assigned to <strong>{{ $organization->name }}</strong>. Organization assignment cannot be changed once set.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <button type="button" 
                            wire:click="cancel"
                            class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        {{ $editingUser ? 'Update User' : 'Create User' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Corporate Conversion Form --}}
    <div x-data="{ open: @entangle('showCorporateForm') }" 
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
                    @if($convertingUser && $convertingUser->user_type === 'corporate')
                        Manage Corporate User Organizations
                    @else
                        Convert User to Corporate
                    @endif
                </h3>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    @if($convertingUser && $convertingUser->user_type === 'corporate')
                        Managing organization access for <strong>{{ $convertingUser->name }}</strong>
                    @else
                        Converting <strong>{{ $convertingUser->name ?? '' }}</strong> to a corporate user with access to multiple organizations
                    @endif
                </p>
            </div>

            <div class="space-y-4">
                {{-- Organization Selection --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Select Organizations *
                    </label>
                    
                    {{-- Search Input --}}
                    <div class="relative mb-3">
                        <input type="text" 
                               wire:model.live="searchQuery" 
                               placeholder="Search organizations..."
                               class="w-full px-3 py-2 pl-10 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-2.5 h-5 w-5 text-neutral-400" />
                    </div>

                    {{-- Selected Organizations Display --}}
                    @if(count($selectedOrganizations) > 0)
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Selected Organizations ({{ count($selectedOrganizations) }})
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedOrganizations as $orgId)
                                    @php
                                        $org = $filteredOrganizations->firstWhere('id', $orgId);
                                    @endphp
                                    @if($org)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300">
                                            {{ $org->name }}
                                            @if($org->id === $organization->id)
                                                <span class="ml-1 text-xs">(Current)</span>
                                            @endif
                                            <button type="button" 
                                                    wire:click="removeOrganization({{ $org->id }})"
                                                    class="ml-2 text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-200">
                                                <x-heroicon-o-x-mark class="h-4 w-4" />
                                            </button>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Searchable Organization List --}}
                    <div class="relative">
                        <div class="border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-700 max-h-48 overflow-y-auto">
                            @forelse($filteredOrganizations ?? [] as $org)
                                @php
                                    $isSelected = in_array($org->id, $selectedOrganizations);
                                @endphp
                                <button type="button"
                                        wire:click="toggleOrganization({{ $org->id }})"
                                        class="w-full px-3 py-2 text-left hover:bg-neutral-50 dark:hover:bg-neutral-600 transition-colors duration-150 {{ $isSelected ? 'bg-sky-50 dark:bg-sky-900/20' : '' }} {{ $org->id === $organization->id ? 'border-l-4 border-l-sky-500' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            @if($isSelected)
                                                <x-heroicon-o-check-circle class="h-5 w-5 text-sky-600 mr-2" />
                                            @else
                                                <x-heroicon-o-circle-stack class="h-5 w-5 text-neutral-400 mr-2" />
                                            @endif
                                            <span class="text-sm text-neutral-700 dark:text-neutral-300">
                                                {{ $org->name }}
                                                @if($org->id === $organization->id)
                                                    <span class="text-xs text-sky-600 font-medium">(Current)</span>
                                                @endif
                                            </span>
                                        </div>
                                        @if($org->is_active)
                                            <span class="text-xs text-green-600 dark:text-green-400">Active</span>
                                        @else
                                            <span class="text-xs text-red-600 dark:text-red-400">Inactive</span>
                                        @endif
                                    </div>
                                </button>
                            @empty
                                <div class="px-3 py-4 text-center text-sm text-neutral-500 dark:text-neutral-400">
                                    @if($searchQuery)
                                        No organizations found matching "{{ $searchQuery }}"
                                    @else
                                        No organizations available
                                    @endif
                                </div>
                            @endforelse
                        </div>
                    </div>
                    
                    @error('selectedOrganizations') 
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Warning (only for conversion) --}}
                @if(!$convertingUser || $convertingUser->user_type !== 'corporate')
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-700/50">
                        <div class="flex items-start gap-3">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" />
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <p class="font-medium mb-1">Important Changes</p>
                                <ul class="text-amber-700 dark:text-amber-300 space-y-1">
                                    <li>• User will become a 'corporate' user type (keeps 'client' role)</li>
                                    <li>• User will have access to all selected organizations</li>
                                    <li>• User will no longer be a primary user for any organization</li>
                                    <li>• This action cannot be easily undone</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200 dark:border-neutral-700">
                    <button type="button" 
                            wire:click="cancelCorporateConversion"
                            class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                        Cancel
                    </button>
                    @if($convertingUser && $convertingUser->user_type === 'corporate')
                        <button type="button" 
                                wire:click="convertToStandard"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Convert to Standard
                        </button>
                        <button type="button" 
                                wire:click="updateCorporateOrganizations"
                                class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                            Update Organizations
                        </button>
                    @else
                        <button type="button" 
                                wire:click="convertToCorporate"
                                class="px-4 py-2 text-sm font-medium text-white bg-amber-600 border border-transparent rounded-md hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                            Convert to Corporate
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Users List --}}
    <div class="space-y-4">
        @forelse ($users as $user)
            <div wire:key="user-{{ $user->id }}"
                 class="bg-white/10 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-4 shadow-md dark:shadow-neutral-200/10 space-y-2 hover:bg-white/15 transition-all duration-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-sky-400 to-sky-600 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">{{ substr($user->name, 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                                                 <div class="flex items-center gap-2 mb-1">
                                     <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100 truncate">
                                         {{ $user->name }}
                                     </h3>
                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                                         Client
                                     </span>
                                     @if($user->user_type === 'corporate')
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                             <x-heroicon-o-building-office class="h-4 w-4 mr-1" />
                                             Corporate ({{ $user->organizations->count() }} orgs)
                                         </span>
                                     @endif
                                     @if($user->isPrimaryForOrganization($organization->id))
                                         <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">
                                             <x-heroicon-o-star class="h-4 w-4 mr-1" />
                                             Primary
                                         </span>
                                     @endif
                                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-red-100 text-red-800 dark:text-red-900/40 dark:text-red-300' }}">
                                         {{ $user->is_active ? 'Active' : 'Inactive' }}
                                     </span>
                                 </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-user class="h-4 w-4" />
                                        <span>{{ $user->username }}</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-envelope class="h-4 w-4" />
                                        <span>{{ $user->email }}</span>
                                    </div>
                                    
                                    @if($user->phone)
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-phone class="h-4 w-4" />
                                        <span>{{ $user->phone }}</span>
                                    </div>
                                    @endif
                                    
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-calendar class="h-4 w-4" />
                                        <span>Joined {{ $user->created_at->format('M d, Y') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2 ml-4">
                                                 {{-- Set as Primary Button (only for client users who are not already primary) --}}
                         @if($user->hasRole('client') && !$user->isPrimaryForOrganization($organization->id))
                             <button wire:click="confirmSetPrimaryUser({{ $user->id }})" 
                                     class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-md transition-colors duration-200"
                                     title="Set as Primary User">
                                 <x-heroicon-o-star class="h-4 w-4" />
                             </button>
                         @endif

                        {{-- Convert to Corporate Button (only for client users) --}}
                        @if($user->hasRole('client') && $user->user_type === 'standard')
                            <button wire:click="showConvertToCorporate({{ $user->id }})" 
                                    class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors duration-200"
                                    title="Convert to Corporate User">
                                <x-heroicon-o-building-office class="h-4 w-4" />
                            </button>
                        @endif

                        {{-- Manage Organizations Button (for existing corporate users) --}}
                        @if($user->user_type === 'corporate')
                            <button wire:click="showManageCorporateOrganizations({{ $user->id }})" 
                                    class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-md transition-colors duration-200"
                                    title="Manage Organization Access">
                                <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                            </button>
                        @endif
                        
                        @can('users.update')
                        <button wire:click="edit({{ $user->id }})" 
                                class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-sky-600 dark:hover:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/20 rounded-md transition-colors duration-200">
                            <x-heroicon-o-pencil class="h-4 w-4" />
                        </button>
                        @endcan
                        
                        @can('users.delete')
                        <button wire:click="confirmDelete({{ $user->id }})" 
                                class="inline-flex items-center p-2 text-neutral-600 dark:text-neutral-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors duration-200">
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                        @endcan
                    </div>
                </div>
                
                                 @if ($deleteId === $user->id)
                     <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                         <div class="flex items-center justify-between">
                             <div>
                                 <h4 class="text-sm font-medium text-red-800 dark:text-red-200">Delete User</h4>
                                 <p class="text-sm text-red-600 dark:text-red-400">Are you sure you want to delete this user? This action cannot be undone.</p>
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

                 {{-- Set Primary User Confirmation --}}
                 @if ($confirmingPrimaryUser === $user->id)
                     <div class="mt-4 p-4 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-md">
                         <div class="flex items-center justify-between">
                             <div>
                                 <h4 class="text-sm font-medium text-purple-800 dark:text-purple-200">Set Primary User</h4>
                                 <p class="text-sm text-purple-600 dark:text-purple-400">
                                     Are you sure you want to set <strong>{{ $user->name }}</strong> as the primary user for <strong>{{ $organization->name }}</strong>? 
                                     This will update the organization's contact information to use this user's email and phone.
                                 </p>
                             </div>
                             <div class="flex items-center gap-2 ml-4">
                                 <button wire:click="setPrimaryUser"
                                         class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                                     Set as Primary
                                 </button>
                                 <button wire:click="cancelSetPrimaryUser"
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
                <x-heroicon-o-users class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-600" />
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">No users found</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">Get started by creating your first client user.</p>
                <div class="mt-6">
                    <button wire:click="create" 
                            class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                        <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                        Create User
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="pt-4">
            {{ $users->links('vendor.pagination.tailwind') }}
        </div>
    @endif
</div>
