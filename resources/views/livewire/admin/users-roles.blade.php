<div class="space-y-6">
    {{-- Page Header --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg p-6 shadow-md">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-neutral-800 dark:text-neutral-100 flex items-center gap-3">
                    <x-heroicon-o-users class="h-8 w-8 text-emerald-500" />
                    Users & Roles
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                    Manage system users and role permissions
                </p>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white/5 backdrop-blur-md border border-neutral-200 dark:border-neutral-200/20 rounded-lg shadow-md">
        <div class="border-b border-neutral-200 dark:border-neutral-200/20">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button
                    wire:click="setTab('users')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'users' 
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' 
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}"
                    aria-current="{{ $activeTab === 'users' ? 'page' : 'false' }}">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-users class="h-5 w-5" />
                        Users
                    </div>
                </button>
                
                <button
                    wire:click="setTab('roles')"
                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 {{ $activeTab === 'roles' 
                        ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' 
                        : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 dark:text-neutral-400 dark:hover:text-neutral-300' }}"
                    aria-current="{{ $activeTab === 'roles' ? 'page' : 'false' }}">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-shield-check class="h-5 w-5" />
                        Roles
                    </div>
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            @if($activeTab === 'users')
                <livewire:admin.manage-users :key="'users-tab'" />
            @elseif($activeTab === 'roles')
                <livewire:admin.manage-roles :key="'roles-tab'" />
            @endif
        </div>
    </div>
</div>