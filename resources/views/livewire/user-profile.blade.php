<div>
    {{-- Profile Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            {{-- Background Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="closeModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0"></div>

            {{-- Modal Dialog --}}
            <div class="inline-block align-bottom bg-white dark:bg-neutral-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                        My Profile
                    </h3>
                    <button wire:click="closeModal" 
                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300 transition-colors">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                {{-- Tab Navigation --}}
                <div class="flex space-x-1 mb-6 bg-neutral-100 dark:bg-neutral-700 p-1 rounded-lg">
                    <button wire:click="switchTab('profile')"
                            class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $tab === 'profile' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}">
                        <x-heroicon-o-user class="h-4 w-4 inline mr-2" />
                        Profile Info
                    </button>
                    <button wire:click="switchTab('password')"
                            class="flex-1 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $tab === 'password' ? 'bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 shadow-sm' : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100' }}">
                        <x-heroicon-o-lock-closed class="h-4 w-4 inline mr-2" />
                        Password
                    </button>
                </div>

                {{-- Profile Tab --}}
                @if($tab === 'profile')
                <form wire:submit="updateProfile" class="space-y-6">
                    
                    {{-- Avatar Upload --}}
                    <div class="text-center">
                        <div class="mx-auto h-24 w-24 relative">
                            @if($previewAvatar)
                                <img src="{{ $previewAvatar }}" alt="Avatar Preview" 
                                     class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-neutral-700 shadow-lg">
                            @elseif(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="Current Avatar" 
                                     class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-neutral-700 shadow-lg">
                            @else
                                <div class="h-24 w-24 rounded-full bg-sky-100 dark:bg-sky-900 flex items-center justify-center border-4 border-white dark:border-neutral-700 shadow-lg">
                                    <span class="text-2xl font-semibold text-sky-700 dark:text-sky-300">
                                        {{ auth()->user()->initials }}
                                    </span>
                                </div>
                            @endif
                            
                            {{-- Upload Overlay --}}
                            <label class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 hover:opacity-100 transition-opacity cursor-pointer">
                                <x-heroicon-o-camera class="h-6 w-6 text-white" />
                                <input type="file" wire:model="newAvatar" accept="image/*" class="hidden">
                            </label>
                        </div>
                        
                        @if($avatar || $previewAvatar)
                        <button type="button" wire:click="removeAvatar" 
                                class="mt-2 text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                            Remove Avatar
                        </button>
                        @endif
                        
                        @error('newAvatar') 
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Name Field --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Full Name
                        </label>
                        <input type="text" id="name" wire:model="name" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('name') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Email Field --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Email Address
                        </label>
                        <input type="email" id="email" wire:model="email" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('email') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Username Field --}}
                    <div>
                        <label for="username" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Username <span class="text-neutral-500">(optional)</span>
                        </label>
                        <input type="text" id="username" wire:model="username" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('username') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
                @endif

                {{-- Password Tab --}}
                @if($tab === 'password')
                <form wire:submit="updatePassword" class="space-y-6">
                    
                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Current Password
                        </label>
                        <input type="password" id="current_password" wire:model="currentPassword" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('currentPassword') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            New Password
                        </label>
                        <input type="password" id="new_password" wire:model="newPassword" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('newPassword') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                            Confirm New Password
                        </label>
                        <input type="password" id="new_password_confirmation" wire:model="newPasswordConfirmation" 
                               class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent">
                        @error('newPasswordConfirmation') 
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> 
                        @enderror
                    </div>

                    {{-- Submit Buttons --}}
                    <div class="flex justify-end space-x-3 pt-4">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-sky-600 border border-transparent rounded-md hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-colors">
                            Update Password
                        </button>
                    </div>
                </form>
                @endif

            </div>
        </div>
    </div>
    @endif
</div>