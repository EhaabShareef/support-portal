<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-neutral-800 dark:text-neutral-100">User Settings</h2>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure user registration, defaults, and security requirements</p>
        </div>
        <div class="flex items-center gap-2">
            @if($hasUnsavedChanges)
                <span class="text-sm text-orange-600 dark:text-orange-400 flex items-center gap-1">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4" />
                    Unsaved changes
                </span>
            @endif
            <button wire:click="saveSettings" 
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-check class="h-4 w-4 mr-2" />
                Save Changes
            </button>
            <button wire:click="resetToDefaults" 
                wire:confirm="Are you sure you want to reset all user settings to their defaults? This cannot be undone."
                class="inline-flex items-center px-3 py-2 bg-neutral-600 hover:bg-neutral-700 text-white text-sm font-medium rounded-md transition-all duration-200">
                <x-heroicon-o-arrow-path class="h-4 w-4 mr-2" />
                Reset All
            </button>
        </div>
    </div>

    {{-- User Registration Settings --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Registration & Default Settings</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure how new users are registered and assigned</p>
        </div>
        
        <div class="p-6 space-y-6">
            {{-- Default Organization --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Default Organization
                    <span class="text-xs text-neutral-500 block mt-1">Organization assigned to new users when none is specified</span>
                </label>
                <select wire:model.live="defaultOrganizationId" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    <option value="">No Default Organization</option>
                    @foreach($this->organizations as $organization)
                        <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                    @endforeach
                </select>
                @error('defaultOrganizationId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Default User Role --}}
            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    Default User Role
                    <span class="text-xs text-neutral-500 block mt-1">Role assigned to new users during registration</span>
                </label>
                <select wire:model.live="defaultUserRole" 
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @foreach($this->userRoleOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('defaultUserRole') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            {{-- Registration & Assignment Toggles --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Allow User Registration --}}
                <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Allow User Registration</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Enable public user registration</div>
                        </div>
                        <button type="button" wire:click="$toggle('allowUserRegistration')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 {{ $allowUserRegistration ? 'bg-sky-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Allow user registration</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $allowUserRegistration ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>

                {{-- Auto Assign to Default Organization --}}
                <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Auto-Assign Default Organization</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Automatically assign new users to default organization</div>
                        </div>
                        <button type="button" wire:click="$toggle('autoAssignToDefaultOrganization')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 {{ $autoAssignToDefaultOrganization ? 'bg-green-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Auto assign to default organization</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoAssignToDefaultOrganization ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Security Settings --}}
    <div class="bg-neutral-50 dark:bg-neutral-800/50 border border-neutral-200 dark:border-neutral-700 rounded-lg">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-lg font-medium text-neutral-800 dark:text-neutral-100">Security Settings</h3>
            <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">Configure password requirements and email verification</p>
        </div>
        
        <div class="p-6 space-y-6">
            {{-- Password Requirements --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Password Min Length --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Minimum Password Length
                        <span class="text-xs text-neutral-500 block mt-1">Minimum characters required for passwords</span>
                    </label>
                    <input type="number" wire:model.live="passwordMinLength" min="6" max="50"
                           class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                    @error('passwordMinLength') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Strong Password Toggle --}}
                <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Require Strong Passwords</div>
                            <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Require uppercase, lowercase, numbers, and symbols</div>
                        </div>
                        <button type="button" wire:click="$toggle('requireStrongPasswords')" 
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 {{ $requireStrongPasswords ? 'bg-red-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                            <span class="sr-only">Require strong passwords</span>
                            <span aria-hidden="true" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireStrongPasswords ? 'translate-x-5' : 'translate-x-0' }}"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Email Verification --}}
            <div class="bg-white dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-600 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-neutral-800 dark:text-neutral-100">Require Email Verification</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Users must verify their email address before accessing the system</div>
                    </div>
                    <button type="button" wire:click="$toggle('requireEmailVerification')" 
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 {{ $requireEmailVerification ? 'bg-blue-600' : 'bg-neutral-200 dark:bg-neutral-700' }}">
                        <span class="sr-only">Require email verification</span>
                        <span aria-hidden="true" 
                              class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireEmailVerification ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>
            </div>

            {{-- Security Summary --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                <div class="flex">
                    <x-heroicon-o-shield-check class="h-5 w-5 text-blue-400 mr-3 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-200">
                        <p class="font-medium mb-1">Current Security Level: {{ $requireStrongPasswords && $requireEmailVerification && $passwordMinLength >= 10 ? 'High' : ($requireStrongPasswords || $requireEmailVerification ? 'Medium' : 'Basic') }}</p>
                        <div class="text-xs">
                            <p>• Password Length: {{ $passwordMinLength }} characters minimum</p>
                            <p>• Strong Passwords: {{ $requireStrongPasswords ? 'Required' : 'Not required' }}</p>
                            <p>• Email Verification: {{ $requireEmailVerification ? 'Required' : 'Optional' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Current Configuration Summary --}}
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
        <div class="px-6 py-4 border-b border-green-200 dark:border-green-800">
            <h3 class="text-lg font-medium text-green-800 dark:text-green-100">Current Configuration</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                <div>
                    <h4 class="font-medium text-green-800 dark:text-green-100 mb-2">Registration</h4>
                    <ul class="space-y-1 text-green-700 dark:text-green-200">
                        <li class="flex items-center">
                            @if($allowUserRegistration)
                                <x-heroicon-o-check class="h-4 w-4 text-green-500 mr-2" />
                                Public registration enabled
                            @else
                                <x-heroicon-o-x-mark class="h-4 w-4 text-red-500 mr-2" />
                                Public registration disabled
                            @endif
                        </li>
                        <li>• Default role: {{ ucfirst($defaultUserRole) }}</li>
                        <li>• Default organization: {{ $defaultOrganizationId ? $this->organizations->find($defaultOrganizationId)?->name : 'None' }}</li>
                        <li class="flex items-center">
                            @if($autoAssignToDefaultOrganization)
                                <x-heroicon-o-check class="h-4 w-4 text-green-500 mr-2" />
                                Auto-assign to default organization
                            @else
                                <x-heroicon-o-x-mark class="h-4 w-4 text-neutral-500 mr-2" />
                                Manual organization assignment
                            @endif
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-medium text-green-800 dark:text-green-100 mb-2">Security</h4>
                    <ul class="space-y-1 text-green-700 dark:text-green-200">
                        <li>• Minimum password length: {{ $passwordMinLength }} characters</li>
                        <li class="flex items-center">
                            @if($requireStrongPasswords)
                                <x-heroicon-o-check class="h-4 w-4 text-green-500 mr-2" />
                                Strong passwords required
                            @else
                                <x-heroicon-o-x-mark class="h-4 w-4 text-yellow-500 mr-2" />
                                Basic passwords allowed
                            @endif
                        </li>
                        <li class="flex items-center">
                            @if($requireEmailVerification)
                                <x-heroicon-o-check class="h-4 w-4 text-green-500 mr-2" />
                                Email verification required
                            @else
                                <x-heroicon-o-x-mark class="h-4 w-4 text-yellow-500 mr-2" />
                                Email verification optional
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Unsaved Changes Warning --}}
@if($hasUnsavedChanges)
<script>
window.addEventListener('beforeunload', function (e) {
    e.preventDefault();
    e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
});
</script>
@endif