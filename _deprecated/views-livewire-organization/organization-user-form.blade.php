<div class="p-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-neutral-800 dark:text-neutral-100">
            {{ $isEditing ? 'Edit Client User' : 'Add New Client User' }}
        </h3>
        <p class="text-sm text-neutral-600 dark:text-neutral-400">
            {{ $isEditing ? 'Update client user information' : 'Create a new client user for this organization' }}
        </p>
        @if(!$isEditing)
            <div class="mt-2 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700/50">
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <x-heroicon-o-information-circle class="h-4 w-4 inline mr-1" />
                    This user will be automatically assigned the "Client" role and belong to {{ $organization->name }}.
                </p>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="space-y-4">
        {{-- Name & Username --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Full Name *
                </label>
                <input type="text" 
                       wire:model.live="form.name" 
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
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                    Letters, numbers, dashes, and underscores only
                </p>
            </div>
        </div>

        {{-- Email --}}
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

        {{-- Password Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    {{ $isEditing ? 'New Password (leave blank to keep current)' : 'Password *' }}
                </label>
                <input type="password" 
                       wire:model="form.password" 
                       id="password"
                       class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                @error('form.password') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
                @if(!$isEditing)
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                        Minimum 8 characters
                    </p>
                @endif
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    {{ $isEditing ? 'Confirm New Password' : 'Confirm Password *' }}
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
            <div>
                <label for="timezone" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                    Timezone
                </label>
                <select wire:model="form.timezone" 
                        id="timezone"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-neutral-700 text-neutral-900 dark:text-neutral-100">
                    <option value="UTC">UTC (Coordinated Universal Time)</option>
                    <option value="America/New_York">Eastern Time (ET)</option>
                    <option value="America/Chicago">Central Time (CT)</option>
                    <option value="America/Denver">Mountain Time (MT)</option>
                    <option value="America/Los_Angeles">Pacific Time (PT)</option>
                    <option value="Europe/London">London (GMT)</option>
                    <option value="Europe/Berlin">Berlin (CET)</option>
                    <option value="Asia/Tokyo">Tokyo (JST)</option>
                    <option value="Asia/Shanghai">Shanghai (CST)</option>
                    <option value="Asia/Kolkata">India (IST)</option>
                    <option value="Indian/Maldives">Maldives (MVT)</option>
                </select>
                @error('form.timezone') 
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                @enderror
            </div>

            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <input type="checkbox" 
                           wire:model="form.is_active" 
                           id="is_active"
                           class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-neutral-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                        User is active
                    </label>
                </div>
            </div>
        </div>

        {{-- Organization Info (Read-only) --}}
        <div class="p-4 bg-neutral-50 dark:bg-neutral-800/50 rounded-lg border border-neutral-200 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <x-heroicon-o-building-office-2 class="h-5 w-5 text-neutral-500" />
                <div>
                    <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                        Organization: {{ $organization->name }}
                    </p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">
                        Role: Client (automatically assigned)
                    </p>
                </div>
            </div>
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
                {{ $isEditing ? 'Update User' : 'Create Client User' }}
            </button>
        </div>
    </form>
</div>